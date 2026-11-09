-- ============================================================
-- Preu personalitzat per setmana + mode de preus
-- Executar a la BD del campus. (Requereix MariaDB 10.0.2+ per IF NOT EXISTS.)
-- ============================================================
ALTER TABLE `weeks`
  ADD COLUMN IF NOT EXISTS `price_club`    decimal(8,2) DEFAULT NULL COMMENT 'Preu de la setmana per a socis (mode weekly)',
  ADD COLUMN IF NOT EXISTS `price_no_club` decimal(8,2) DEFAULT NULL COMMENT 'Preu de la setmana per a no socis (mode weekly)';

-- 'weekly' = preu per setmana (nou); 'tiered' = barem antic per modalitat (desactivat, es pot reactivar a Configuració)
INSERT IGNORE INTO `site_settings` (`key`, `value`) VALUES ('pricing_mode', 'weekly');
