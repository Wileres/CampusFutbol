# Correus, SMTP i crons

## SMTP
`src/mailer.php` (`_buildMailer()`) usa PHPMailer amb `SMTP_HOST/PORT/USER/PASS`, STARTTLS i remitent `SMTP_FROM_EMAIL` / `SMTP_FROM_NAME` (Brevo: `smtp-relay.brevo.com:587`).
El remitent ha d'estar validat al proveïdor, o els correus es descarten.

| Funció | Quan s'envia | Destinataris |
|---|---|---|
| `sendConfirmationEmail()` | `reg-confirm` | Pare/mare de la inscripció (total + IBAN + instruccions) |
| `sendPasswordResetEmail()` | `forgot-password` | Usuari que ho demana |
| `sendTestEmail()` | `manager-test-email` | Adreça indicada pel gestor |
| `sendWeeklyEmail()` | Cron de diumenge | Pares amb inscripció **confirmed** que inclou la setmana |
| `sendWeeklyTestEmail()` | `manager-weekly-content&send_test=1` i cron de prova | **Només** usuaris `manager`; assumpte amb `[PROVA]`; no toca `email_sent` |

## On veure els correus enviats
`logs/smtp.log` (protegit per `logs/.htaccess`). Cada enviament deixa destinatari, èxit/error i la conversa SMTP segons `SMTP_DEBUG`.
També és útil el panell del proveïdor (Brevo → Transactional → Logs).

## Programa setmanal (cron)
`cron/send_weekly.php`:
1. Calcula el **proper dilluns** (si és diumenge, demà).
2. Cerca la setmana activa amb `start_date` = aquell dilluns **i** fila a `weekly_content`.
3. Surt si `email_sent = 1` («Correu ja enviat…») o si no hi ha cap text.
4. Envia als destinataris i, si tot va bé, posa `email_sent = 1`.

Per reenviar: `UPDATE weekly_content SET email_sent = 0 WHERE week_id = X;` (o tornar a desar el contingut des del gestor, que també el reinicia).

### Crontab (ISPConfig; PHP 8.4)
```cron
# Real: diumenge 20:00
0 20 * * 0 /usr/bin/php8.4 /var/www/clients/client1/web9/web/futbol-camp/cron/send_weekly.php >> /var/www/clients/client1/web9/web/futbol-camp/logs/cron.log 2>&1

# Prova (només gestors): diumenge 19:00
0 19 * * 0 /usr/bin/php8.4 /var/www/clients/client1/web9/web/futbol-camp/cron/send_weekly_test.php >> /var/www/clients/client1/web9/web/futbol-camp/logs/cron_test.log 2>&1
```
> ⚠️ El camí és `<arrel del projecte>/cron/…` (abans algun comentari deia `futbol-camp/../cron/`, que és incorrecte si `cron/` és dins `futbol-camp/`).
> Verifiqueu que l'arrel real al servidor és la que hi ha a `config.php`/FTP. Els logs de cron a `logs/` queden protegits i fora de git.

Execució manual: `/usr/bin/php8.4 /ruta/al/projecte/cron/send_weekly.php`.
