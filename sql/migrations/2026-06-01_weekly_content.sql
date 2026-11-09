-- ============================================================
-- Taula per al menú i activitats setmanals
-- Executar a la BD c1futbolcamp
-- ============================================================
CREATE TABLE IF NOT EXISTS `weekly_content` (
  `id`              int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `week_id`         int(10) UNSIGNED NOT NULL,
  `menu_dilluns`    text DEFAULT NULL,
  `menu_dimarts`    text DEFAULT NULL,
  `menu_dimecres`   text DEFAULT NULL,
  `menu_dijous`     text DEFAULT NULL,
  `menu_divendres`  text DEFAULT NULL,
  `act_dilluns`     text DEFAULT NULL,
  `act_dimarts`     text DEFAULT NULL,
  `act_dimecres`    text DEFAULT NULL,
  `act_dijous`      text DEFAULT NULL,
  `act_divendres`   text DEFAULT NULL,
  `email_sent`      tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = correu diumenge ja enviat',
  `updated_at`      datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_week` (`week_id`),
  CONSTRAINT `fk_wc_week` FOREIGN KEY (`week_id`) REFERENCES `weeks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
