<?php
/**
 * cron/send_weekly_test.php
 *
 * Versió de PROVA del cron setmanal.
 * Envia el programa de la setmana entrant ÚNICAMENT als usuaris amb rol 'manager'.
 * No modifica el camp email_sent ni afecta l'enviament real.
 *
 * Ús manual:
 *   /usr/bin/php8.4 /var/www/clients/client1/web9/web/futbol-camp/cron/send_weekly_test.php
 *
 * O com a cron de prova (p.ex. cada diumenge a les 19:00, una hora abans del real):
 *   0 19 * * 0 /usr/bin/php8.4 /var/www/clients/client1/web9/web/futbol-camp/cron/send_weekly_test.php >> /var/log/campus_cron_test.log 2>&1
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/config.php';
require APP_ROOT . '/src/db.php';
require APP_ROOT . '/src/settings.php';
require APP_ROOT . '/src/mailer.php';

$log = static fn(string $msg) => print('[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL);

$log('=== Inici cron PROVA send_weekly_test.php ===');

// Calcular el proper dilluns (igual que el cron real)
$today      = new DateTimeImmutable();
$dayOfWeek  = (int)$today->format('N');
$daysAhead  = $dayOfWeek === 7 ? 1 : (8 - $dayOfWeek);
$nextMonday = $today->modify("+{$daysAhead} days")->format('Y-m-d');
$log("Proper dilluns: {$nextMonday}");

try {
    $db = getDB();

    // Cercar la setmana i el seu contingut
    $st = $db->prepare("
        SELECT w.id, w.label, w.start_date, w.end_date,
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

    $log("Enviant correu de PROVA als gestors...");
    $result = sendWeeklyTestEmail($week, $row);

    if (!empty($result['recipients'])) {
        $log("Enviats: {$result['sent']} | Errors: {$result['errors']}");
        $log("Destinataris: " . implode(', ', $result['recipients']));
    } else {
        $log("No hi ha cap usuari gestor al sistema.");
    }

} catch (Throwable $e) {
    $log('ERROR: ' . $e->getMessage() . ' a ' . $e->getFile() . ':' . $e->getLine());
    exit(1);
}

$log('=== Fi cron PROVA send_weekly_test.php ===');
exit(0);
