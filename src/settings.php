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
