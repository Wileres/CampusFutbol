<?php
/**
 * src/mailer.php – PHPMailer wrapper amb captura de log SMTP
 *
 * L'autoloader es carrega UNA SOLA VEGADA aquí dalt (fora de les funcions)
 * perquè require_once dins d'una funció pot fallar si el fitxer ja ha estat
 * marcat com a inclòs en el mateix procés PHP.
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// ── Buffer de log de l'última connexió SMTP ──────────────────────────────────
// Accessible via mailerLastLog() des de qualsevol lloc del request.
$_MAILER_LOG = '';

function mailerLastLog(): string
{
    global $_MAILER_LOG;
    return $_MAILER_LOG;
}

function _appendMailerLog(string $msg, int $level): void
{
    global $_MAILER_LOG;
    $prefix = match ($level) {
        0 => '[EXCEPTION]',
        1 => '[CLIENT]   ',
        2 => '[SERVER]   ',
        3 => '[CONNECT]  ',
        4 => '[LOWLEVEL] ',
        default => '[INFO]     ',
    };
    $line = date('H:i:s') . " {$prefix} " . trim($msg);
    $_MAILER_LOG .= $line . "\n";

    // Escriu sempre al log del servidor (visible a error_log / Apache error.log)
    error_log('[SMTP] ' . $line);
}

// ── Ruta del fitxer de log persistent ────────────────────────────────────────
function mailerLogFile(): string
{
    // Fora del web root per seguretat
    return dirname(__DIR__) . '/logs/smtp.log';
}

function _writeMailerLogFile(string $recipient, bool $success, string $log): void
{
    $dir = dirname(mailerLogFile());
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
        // Protegir el directori
        file_put_contents($dir . '/.htaccess', "Deny from all\n");
    }
    $entry  = str_repeat('─', 72) . "\n";
    $entry .= date('Y-m-d H:i:s') . " | To: {$recipient} | " . ($success ? 'OK' : 'ERROR') . "\n";
    $entry .= $log;
    $entry .= "\n";
    file_put_contents(mailerLogFile(), $entry, FILE_APPEND | LOCK_EX);
}

// ── Constructor del mailer ────────────────────────────────────────────────────
function _buildMailer(): PHPMailer
{
    global $_MAILER_LOG;
    $_MAILER_LOG = ''; // Reset per a cada enviament

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

    // Activa la captura de la conversa SMTP
    $mail->SMTPDebug  = defined('SMTP_DEBUG') ? (int)SMTP_DEBUG : 2;
    $mail->Debugoutput = '_appendMailerLog';

    return $mail;
}

// ── Envia correu de confirmació d'inscripció ──────────────────────────────────
function sendConfirmationEmail(array $reg, array $selWeeks, array $pricing): bool
{
    try {
        $mail      = _buildMailer();
        $recipient = $reg['parent_email'];
        $mail->addAddress($recipient, $reg['parent_name']);

        $childName = htmlspecialchars($reg['child_first_name'] . ' ' . $reg['child_last_name']);
        $photosTxt = $reg['allow_photos'] ? 'Autoritzat' : 'No autoritzat';
        $totalFmt  = number_format($pricing['total'], 2, ',', '.') . ' €';

        $weeksRows = '';
        foreach ($selWeeks as $w)
            $weeksRows .= '<tr><td>' . htmlspecialchars($w['label']) . '</td><td>' . htmlspecialchars(modalityLabel($w['modality'])) . '</td></tr>';

        $priceRows = '';
        foreach ($pricing['breakdown'] as $mod => $data)
            $priceRows .= '<tr><td>' . htmlspecialchars(modalityLabel($mod)) . '</td>'
                . '<td style="text-align:center">' . $data['count'] . ' set.</td>'
                . '<td style="text-align:right">' . number_format($data['subtotal'], 2, ',', '.') . ' €</td></tr>';

        $mail->Subject = 'Confirmació d\'inscripció – ' . SITE_NAME;
        $mail->isHTML(true);
        $mail->Body = <<<HTML
<!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#1a1a2e;background:#f8f9fa;margin:0;padding:20px">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1)">
  <div style="background:#0d6efd;padding:30px 40px">
    <h1 style="color:#fff;margin:0;font-size:22px">⚽ {$reg['site_name']}</h1>
    <p style="color:#cfe2ff;margin:5px 0 0">Confirmació d'inscripció</p>
  </div>
  <div style="padding:30px 40px">
    <p>Hola <strong>{$reg['parent_name']}</strong>,</p>
    <p>La inscripció de <strong>{$childName}</strong> ha estat confirmada.</p>
    <h3 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:6px">Setmanes seleccionades</h3>
    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px">
      <thead><tr style="background:#0d6efd;color:#fff"><th style="text-align:left">Setmana</th><th style="text-align:left">Modalitat</th></tr></thead>
      <tbody>{$weeksRows}</tbody>
    </table>
    <h3 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:6px;margin-top:24px">Resum econòmic</h3>
    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px">
      <thead><tr style="background:#0d6efd;color:#fff"><th style="text-align:left">Modalitat</th><th style="text-align:center">Set.</th><th style="text-align:right">Subtotal</th></tr></thead>
      <tbody>{$priceRows}</tbody>
      <tfoot><tr style="background:#e8f0fe;font-weight:bold"><td colspan="2">TOTAL</td><td style="text-align:right">{$totalFmt}</td></tr></tfoot>
    </table>
    <h3 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:6px;margin-top:24px">Autoritzacions</h3>
    <table cellpadding="6" cellspacing="0" style="font-size:14px">
      <tr><td><strong>Imatge a xarxes socials:</strong></td><td>{$photosTxt}</td></tr>
      <tr><td><strong>Tractament de dades (RGPD):</strong></td><td>Acceptat</td></tr>
    </table>
    <h3 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:6px;margin-top:24px">Informació de pagament</h3>
    <table cellpadding="6" cellspacing="0" style="font-size:14px">
      <tr><td><strong>Beneficiari:</strong></td><td>{$reg['payment_beneficiary']}</td></tr>
      <tr><td><strong>IBAN:</strong></td><td style="font-family:monospace">{$reg['iban']}</td></tr>
      <tr><td><strong>Concepte:</strong></td><td>{$childName}</td></tr>
      <tr><td><strong>Import:</strong></td><td>{$totalFmt}</td></tr>
    </table>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:12px;margin-top:16px;font-size:14px">
      ⚠️ {$reg['payment_instructions']}
    </div>
    <p style="margin-top:24px;font-size:13px;color:#6c757d"><em>Correu generat automàticament.</em></p>
  </div>
  <div style="background:#f1f3f5;padding:16px 40px;font-size:12px;color:#868e96;text-align:center">© {$reg['site_name']}</div>
</div></body></html>
HTML;
        $mail->AltBody = "Confirmació de {$childName}. Total: {$totalFmt}. IBAN: " . IBAN . "\n" . PAYMENT_INSTRUCTIONS;
        $mail->send();
        _writeMailerLogFile($recipient, true, mailerLastLog());
        return true;
    } catch (Throwable $e) {
        _appendMailerLog('EXCEPTION: ' . $e->getMessage(), 0);
        _writeMailerLogFile($reg['parent_email'] ?? '?', false, mailerLastLog());
        return false;
    }
}

// ── Envia correu de restabliment de contrasenya ───────────────────────────────
function sendPasswordResetEmail(string $toEmail, string $toName, string $plainToken): bool
{
    try {
        $mail = _buildMailer();
        $mail->addAddress($toEmail, $toName);
        $resetUrl = SITE_URL . '?p=reset-password&token=' . urlencode($plainToken);

        $mail->Subject = 'Restabliment de contrasenya – ' . SITE_NAME;
        $mail->isHTML(true);
        $mail->Body = <<<HTML
<!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#1a1a2e;background:#f8f9fa;margin:0;padding:20px">
<div style="max-width:500px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1)">
  <div style="background:#0d6efd;padding:24px 32px">
    <h1 style="color:#fff;margin:0;font-size:20px">⚽ Camp de Futbol</h1>
    <p style="color:#cfe2ff;margin:4px 0 0;font-size:14px">Restabliment de contrasenya</p>
  </div>
  <div style="padding:28px 32px">
    <p>Hem rebut una sol·licitud per restablir la contrasenya del vostre compte.</p>
    <p style="text-align:center;margin:28px 0">
      <a href="{$resetUrl}" style="background:#0d6efd;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block">
        Restablir la contrasenya
      </a>
    </p>
    <p style="font-size:13px;color:#6c757d">O copieu: <a href="{$resetUrl}" style="color:#0d6efd;word-break:break-all">{$resetUrl}</a></p>
    <div style="background:#f8d7da;border:1px solid #f5c2c7;border-radius:6px;padding:10px;font-size:13px;margin-top:16px">
      ⚠️ Caduca en <strong>60 minuts</strong> i és d'un sol ús.
    </div>
  </div>
</div></body></html>
HTML;
        $mail->AltBody = "Restabliu la contrasenya: {$resetUrl}\n(Caduca en 60 minuts)";
        $mail->send();
        _writeMailerLogFile($toEmail, true, mailerLastLog());
        return true;
    } catch (Throwable $e) {
        _appendMailerLog('EXCEPTION: ' . $e->getMessage(), 0);
        _writeMailerLogFile($toEmail, false, mailerLastLog());
        return false;
    }
}

// ── Envia correu de prova (ús exclusiu del gestor) ────────────────────────────
function sendTestEmail(string $toEmail): array
{
    try {
        $mail = _buildMailer();
        $mail->addAddress($toEmail);
        $mail->Subject = '[PROVA] ' . SITE_NAME . ' – Test SMTP ' . date('d/m/Y H:i:s');
        $mail->isHTML(true);
        $mail->Body = '<p>Aquest és un correu de prova enviat des de <strong>' . htmlspecialchars(SITE_NAME) . '</strong> el ' . date('d/m/Y \a \l\e\s H:i:s') . '.</p>'
            . '<p>Si rebeu aquest missatge, la configuració SMTP és correcta.</p>'
            . '<hr><p style="font-size:12px;color:#999">Host: ' . htmlspecialchars(SMTP_HOST) . ':' . SMTP_PORT . ' · From: ' . htmlspecialchars(SMTP_FROM_EMAIL) . '</p>';
        $mail->AltBody = 'Correu de prova de ' . SITE_NAME . '. Si rebeu aquest missatge, el SMTP funciona correctament.';
        $mail->send();
        _writeMailerLogFile($toEmail, true, mailerLastLog());
        return ['success' => true, 'log' => mailerLastLog()];
    } catch (Throwable $e) {
        _appendMailerLog('EXCEPTION: ' . $e->getMessage(), 0);
        _writeMailerLogFile($toEmail, false, mailerLastLog());
        return ['success' => false, 'log' => mailerLastLog(), 'error' => $e->getMessage()];
    }
}

// ── Correu setmanal: Menú + Activitats ───────────────────────────────────────

/**
 * Construeix el cos HTML del correu setmanal.
 * Reutilitzat tant per enviar com per previsualitzar.
 */
function buildWeeklyEmailHtml(array $week, array $wc): string
{
    $siteName  = SITE_NAME;
    $dateRange = date('d/m/Y', strtotime($week['start_date']))
               . ' – '
               . date('d/m/Y', strtotime($week['end_date']));

    $days = ['dilluns'=>'Dilluns','dimarts'=>'Dimarts','dimecres'=>'Dimecres','dijous'=>'Dijous','divendres'=>'Divendres'];

    // ── Taula Menú ──
    $menuRows = '';
    $hasMenu  = false;
    foreach ($days as $key => $label) {
        $val = trim($wc["menu_{$key}"] ?? '');
        if ($val) $hasMenu = true;
        $offset  = array_search($key, array_keys($days));
        $dateStr = date('d/m', strtotime($week['start_date']) + $offset * 86400);
        $cell    = $val
            ? nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'))
            : '<em style="color:#adb5bd">Pendent</em>';
        $menuRows .= "<tr>
            <td style='padding:10px 14px;border-bottom:1px solid #e8f0fe;white-space:nowrap;vertical-align:top'>
                <strong style='color:#0d6efd'>{$label}</strong><br>
                <small style='color:#adb5bd'>{$dateStr}</small>
            </td>
            <td style='padding:10px 14px;border-bottom:1px solid #e8f0fe;line-height:1.65'>{$cell}</td>
        </tr>";
    }

    // ── Taula Activitats ──
    $actRows = '';
    $hasAct  = false;
    foreach ($days as $key => $label) {
        $val = trim($wc["act_{$key}"] ?? '');
        if ($val) $hasAct = true;
        $offset  = array_search($key, array_keys($days));
        $dateStr = date('d/m', strtotime($week['start_date']) + $offset * 86400);
        $cell    = $val
            ? nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'))
            : '<em style="color:#adb5bd">Pendent</em>';
        $actRows .= "<tr>
            <td style='padding:10px 14px;border-bottom:1px solid #e8f0fe;white-space:nowrap;vertical-align:top'>
                <strong style='color:#0d6efd'>{$label}</strong><br>
                <small style='color:#adb5bd'>{$dateStr}</small>
            </td>
            <td style='padding:10px 14px;border-bottom:1px solid #e8f0fe;line-height:1.65'>{$cell}</td>
        </tr>";
    }

    $seccioMenu = $hasMenu ? "
        <h2 style='font-family:Arial,sans-serif;color:#0d6efd;font-size:1.15rem;margin:24px 0 10px;border-bottom:2px solid #cfe2ff;padding-bottom:6px'>
            🍽️ Menú de la setmana
        </h2>
        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(13,110,253,.08)'>
            {$menuRows}
        </table>" : '';

    $seccioAct = $hasAct ? "
        <h2 style='font-family:Arial,sans-serif;color:#0d6efd;font-size:1.15rem;margin:28px 0 10px;border-bottom:2px solid #cfe2ff;padding-bottom:6px'>
            ⚽ Activitats de la setmana
        </h2>
        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(13,110,253,.08)'>
            {$actRows}
        </table>" : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="ca">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5fb;font-family:Arial,sans-serif;font-size:14px;color:#1a1a2e">
<div style="max-width:640px;margin:20px auto;background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(13,110,253,.12);overflow:hidden">

    <div style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);padding:28px 36px">
        <div style="font-size:2rem;margin-bottom:6px">⚽</div>
        <h1 style="color:#fff;font-size:1.25rem;margin:0;font-weight:700">{$siteName}</h1>
        <p style="color:#cfe2ff;margin:6px 0 0;font-size:.9rem">Programa de la setmana</p>
    </div>

    <div style="padding:28px 36px">
        <div style="background:#e8f0fe;border-left:4px solid #0d6efd;border-radius:0 6px 6px 0;padding:12px 16px;margin-bottom:20px">
            <strong style="color:#0a58ca;font-size:1rem">📅 {$week['label']}</strong><br>
            <span style="color:#495057;font-size:.85rem">{$dateRange} &nbsp;·&nbsp; Dilluns a Divendres</span>
        </div>

        <p style="color:#495057;line-height:1.65">
            Benvolgudes famílies,<br><br>
            Us fem arribar el programa de la setmana que comença. Trobareu el menú de migdia i les activitats previstes per a cada dia.
        </p>

        {$seccioMenu}
        {$seccioAct}

        <p style="margin-top:28px;color:#adb5bd;font-size:.8rem;border-top:1px solid #e8f0fe;padding-top:14px">
            Fins dilluns! 💪 &nbsp;—&nbsp; {$siteName}<br>
            <em>Correu enviat automàticament. Si us plau, no respongueu directament a aquest missatge.</em>
        </p>
    </div>

    <div style="background:#f8f9fa;padding:14px 36px;text-align:center;color:#adb5bd;font-size:.75rem;border-top:1px solid #e8f0fe">
        © <?= date('Y') ?> {$siteName}
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Envia el correu setmanal a tots els pares amb inscripcions confirmades
 * a la setmana indicada.
 *
 * @param array $week  Fila de la taula `weeks`
 * @param array $wc    Fila de la taula `weekly_content`
 * @param bool  $force Si true, envia tot i que email_sent=1 (per a proves)
 * @return array ['sent'=>int, 'errors'=>int]
 */
function sendWeeklyEmail(array $week, array $wc, bool $force = false): array
{
    $db = getDB();

    // Destinataris únics: pares amb inscripció confirmed a aquesta setmana
    $st = $db->prepare("
        SELECT DISTINCT u.name, u.email
        FROM registration_weeks rw
        JOIN registrations r  ON r.id  = rw.registration_id
        JOIN users u          ON u.id  = r.parent_user_id
        WHERE rw.week_id = :wid
          AND r.status   = 'confirmed'
          AND u.email   IS NOT NULL
    ");
    $st->execute([':wid' => $week['id']]);
    $recipients = $st->fetchAll();

    if (empty($recipients)) {
        return ['sent' => 0, 'errors' => 0, 'skipped' => 'no_recipients'];
    }

    $html     = buildWeeklyEmailHtml($week, $wc);
    $subject  = '📅 Programa ' . $week['label'] . ' – ' . SITE_NAME;
    $sent     = 0;
    $errors   = 0;

    foreach ($recipients as $r) {
        try {
            $mail = _buildMailer();
            $mail->addAddress($r['email'], $r['name']);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $html;
            $mail->AltBody = "Programa {$week['label']} ({$week['start_date']} – {$week['end_date']}). "
                           . "Consulta el correu HTML per veure el menú i les activitats. — " . SITE_NAME;
            $mail->send();
            _writeMailerLogFile($r['email'], true, mailerLastLog());
            $sent++;
        } catch (Throwable $e) {
            _appendMailerLog('EXCEPTION: ' . $e->getMessage(), 0);
            _writeMailerLogFile($r['email'] ?? '?', false, mailerLastLog());
            $errors++;
        }
    }

    // Marcar com enviat (si no és una prova)
    if (!$force && $sent > 0) {
        $db->prepare("UPDATE weekly_content SET email_sent = 1 WHERE week_id = :wid")
           ->execute([':wid' => $week['id']]);
    }

    return ['sent' => $sent, 'errors' => $errors];
}

/**
 * Envia el correu setmanal de PROVA únicament als usuaris amb rol 'manager'.
 * No modifica el camp email_sent.
 *
 * @return array ['sent'=>int, 'errors'=>int, 'recipients'=>string[]]
 */
function sendWeeklyTestEmail(array $week, array $wc): array
{
    $db = getDB();

    $managers = $db->query("SELECT name, email FROM users WHERE role = 'manager' AND email IS NOT NULL")
                   ->fetchAll();

    if (empty($managers)) {
        return ['sent' => 0, 'errors' => 0, 'recipients' => []];
    }

    $html    = buildWeeklyEmailHtml($week, $wc);
    $subject = '[PROVA] 📅 Programa ' . $week['label'] . ' – ' . SITE_NAME;
    $sent    = 0;
    $errors  = 0;
    $rcpts   = [];

    foreach ($managers as $m) {
        try {
            $mail = _buildMailer();
            $mail->addAddress($m['email'], $m['name']);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $html;
            $mail->AltBody = "[PROVA] Programa {$week['label']}. Consulta el correu HTML per veure el contingut. — " . SITE_NAME;
            $mail->send();
            _writeMailerLogFile($m['email'], true, mailerLastLog());
            $sent++;
            $rcpts[] = $m['email'];
        } catch (Throwable $e) {
            _appendMailerLog('EXCEPTION: ' . $e->getMessage(), 0);
            _writeMailerLogFile($m['email'] ?? '?', false, mailerLastLog());
            $errors++;
        }
    }

    return ['sent' => $sent, 'errors' => $errors, 'recipients' => $rcpts];
}
