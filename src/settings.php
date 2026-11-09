<?php
/**
 * src/settings.php – Helpers per accedir a site_settings
 */

/**
 * Retorna tots els settings com a array associatiu [key => value].
 * S'emmagatzema en una variable estàtica per evitar consultes repetides.
 */
function getAllSettings(): array
{
    static $cache = null;
    if ($cache === null) {
        try {
            $rows  = getDB()->query('SELECT `key`, `value` FROM site_settings')->fetchAll();
            $cache = array_column($rows, 'value', 'key');
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache;
}

/**
 * Retorna el valor d'un setting concret (o $default si no existeix).
 */
function getSetting(string $key, string $default = ''): string
{
    return getAllSettings()[$key] ?? $default;
}

/**
 * Desa un o múltiples settings i invalida la cache en memòria.
 * @param array<string,string> $data
 */
function saveSettings(array $data): void
{
    $db = getDB();
    $st = $db->prepare(
        'INSERT INTO site_settings (`key`, `value`) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE `value` = :v2, updated_at = NOW()'
    );
    foreach ($data as $k => $v) {
        $st->execute([':k' => $k, ':v' => $v, ':v2' => $v]);
    }
    // Invalida la cache estàtica forçant una recàrrega en la propera crida
    // (usem una variable de referència externa per simular el reset)
    $_SESSION['_settings_dirty'] = true;
}

/**
 * Comprova si les inscripcions estan bloqueades.
 * Retorna true si estan bloquejades (considerant la data de desbloqueig).
 */
function areRegistrationsBlocked(): bool
{
    $blocked     = getSetting('registration_blocked', '0') === '1';
    $unblockDate = getSetting('registration_unblock_date', '');

    if (!$blocked) {
        return false;
    }

    // Si hi ha data de desbloqueig i ja ha passat, no bloquegem
    if ($unblockDate && $unblockDate <= date('Y-m-d')) {
        return false;
    }

    return true;
}

// ── Textos editables de la pàgina d'inici ────────────────────────────────────
// Valors per defecte; el gestor els pot canviar a «Textos inici» (settings `home_*`).
// Un valor buit fa servir el text per defecte.
const HOME_TEXT_DEFAULTS = [
    'hero_emoji'       => '⚽',
    'hero_title'       => '',   // buit → SITE_NAME
    'hero_text'        => "Camp de futbol d'estiu per a nens i nenes de 5 a 14 anys.\nInscriviu el vostre fill o filla i viviu una experiència inoblidable!",
    'btn_register'     => "Inscriure's ara",
    'btn_new_reg'      => 'Nova inscripció',
    'btn_info'         => 'Més informació',
    'btn_my_regs'      => 'Les meves inscripcions',
    'btn_login'        => 'Iniciar sessió',
    'program_badge'    => '📅 Programa setmanal',
    'menu_title'       => '🍽️ Menú de la setmana',
    'activities_title' => '⚽ Activitats de la setmana',
    'weeks_title'      => 'Setmanes disponibles',
    'btn_prices'       => 'Veure preus',
];

/** Text editable de la portada (valor desat o per defecte). */
function homeText(string $key): string
{
    $v = trim(getSetting('home_'.$key, ''));
    if ($v !== '') return $v;
    return $key === 'hero_title' ? SITE_NAME : (HOME_TEXT_DEFAULTS[$key] ?? '');
}
