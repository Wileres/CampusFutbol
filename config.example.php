<?php
/**
 * config.example.php – plantilla de configuració.
 *
 *   cp config.example.php config.php
 *
 * `config.php` conté secrets (BD, SMTP) i està al .gitignore: MAI no s'ha de pujar al repositori.
 * Totes les constants són obligatòries (les carrega public/index.php, setup.php i cron/*.php).
 */

// ── Base de dades ───────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'c1futbolcamp');
define('DB_USER',    'canvia_usuari');
define('DB_PASS',    'canvia_contrasenya');
define('DB_CHARSET', 'utf8mb4');

// ── Correu (PHPMailer + SMTP, p. ex. Brevo) ─────────────────────────────────
define('SMTP_HOST',       'smtp-relay.brevo.com');
define('SMTP_PORT',       587);
define('SMTP_AUTH',       true);
define('SMTP_USER',       'usuari-smtp');
define('SMTP_PASS',       'clau-smtp');
define('SMTP_FROM_EMAIL', 'campus@example.com');
define('SMTP_FROM_NAME',  'Campus de Futbol');
// Nivell de depuració SMTP (0 = res, 2 = client+servidor). Es registra a logs/smtp.log.
// Nota: src/mailer.php llegeix SMTP_DEBUG (si no existeix, usa 2). Poseu 0 en producció quan tot funcioni.
define('SMTP_DEBUG',      2);

// ── Pagament per transferència (s'inclou al correu de confirmació) ──────────
define('IBAN',                'ES00 0000 0000 0000 0000 0000');
define('PAYMENT_BENEFICIARY', 'Nom de l\'entitat beneficiària');
define('PAYMENT_INSTRUCTIONS',
    'Feu la transferència a l\'IBAN indicat posant com a concepte el nom del nen/a. '
  . 'El pagament s\'ha d\'efectuar abans de l\'inici del campus.'
);

// ── Web ─────────────────────────────────────────────────────────────────────
// URL de l'arrel pública (document root = public/), SENSE barra final:
// el codi genera `SITE_URL?p=...` i `SITE_URL/assets/...`.
//   Local (php -S):  http://localhost:8000
//   Producció:       https://campusfutbol.example.com
define('SITE_URL',  'http://localhost:8000');
define('SITE_NAME', 'Campus de Futbol');

// Reservat (actualment no s'utilitza al codi).
define('APP_SECRET', 'genera-una-cadena-aleatoria-llarga');

// Google Analytics 4 (deixar buit per desactivar).
define('GA_MEASUREMENT_ID', '');
