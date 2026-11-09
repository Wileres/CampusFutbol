<?php
/**
 * cron/send_weekly.php
 *
 * Executa cada diumenge a les 20:00 via cron:
 *   0 20 * * 0 /usr/bin/php8.4 /var/www/clients/client1/web9/web/futbol-camp/cron/send_weekly.php >> /var/log/campus_cron.log 2>&1
 *
 * Busca la setmana que comença el proper dilluns, comprova que tingui
 * contingut publicat i que el correu no hagi estat enviat, i envia
 * a tots els pares amb inscripcions confirmed.
 */

// Carregar l'aplicació sense sessió web
define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/config.php';
require APP_ROOT . '/src/db.php';
require APP_ROOT . '/src/settings.php';
require APP_ROOT . '/src/mailer.php';

$log = static fn(string $msg) => print('[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL);

$log('=== Inici cron send_weekly.php ===');

// Calcular el proper dilluns
$today     = new DateTimeImmutable();
$dayOfWeek = (int)$today->format('N'); // 1=dll … 7=diu
$daysAhead = $dayOfWeek === 7 ? 1 : (8 - $dayOfWeek);
$nextMonday = $today->modify("+{$daysAhead} days")->format('Y-m-d');
$log("Proper dilluns: {$nextMonday}");

try {
    $db = getDB();

    // Cercar la setmana i el seu contingut
    $st = $db->prepare("
        SELECT w.id, w.label, w.start_date, w.end_date,
               wc.id AS wc_id, wc.email_sent,
               wc.menu_dilluns,  wc.menu_dimarts,  wc.menu_dimecres,  wc.menu_dijous,  wc.menu_divendres,
               wc.act_dilluns,   wc.act_dimarts,   wc.act_dimecres,   wc.act_dijous,   wc.act_divendres
        FROM weeks w
        INNER JOIN weekly_content wc ON wc.week_id = w.id
        WHERE w.start_date = :monday
          AND w.active = 1
        LIMIT 1
    ");
    $st->execute([':monday' => $nextMonday]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $log("Cap setmana activa amb contingut pel dilluns {$nextMonday}. Res a fer.");
        exit(0);
    }

    $log("Setmana trobada: [{$row['id']}] {$row['label']}");

    if ($row['email_sent']) {
        $log("Correu ja enviat per a aquesta setmana (email_sent=1). Sortint.");
        exit(0);
    }

    // Verificar que hi hagi contingut real
    $hasContent = false;
    foreach (['dilluns','dimarts','dimecres','dijous','divendres'] as $d) {
        if (!empty(trim($row["menu_{$d}"] ?? '')) || !empty(trim($row["act_{$d}"] ?? ''))) {
            $hasContent = true;
            break;
        }
    }
    if (!$hasContent) {
        $log("La setmana no té contingut (menú/activitats). No s'envia correu.");
        exit(0);
    }

    $week = ['id'=>$row['id'],'label'=>$row['label'],'start_date'=>$row['start_date'],'end_date'=>$row['end_date']];
    $wc   = $row; // conté tots els camps menu_* i act_*

    $log("Enviant correus...");
    $result = sendWeeklyEmail($week, $wc, force: false);

    $log("Enviats: {$result['sent']} | Errors: {$result['errors']}");

    if (isset($result['skipped'])) {
        $log("Omès: {$result['skipped']}");
    }

} catch (Throwable $e) {
    $log('ERROR: ' . $e->getMessage() . ' a ' . $e->getFile() . ':' . $e->getLine());
    exit(1);
}

$log('=== Fi cron send_weekly.php ===');
exit(0);
