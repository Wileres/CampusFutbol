-- ============================================================================
-- Campus de Futbol – esquema complet de la base de dades (instal·lació nova)
-- MySQL 8 / MariaDB 10.6+ · utf8mb4 · InnoDB
--
-- NOMÉS estructura i valors per defecte NO personals.
-- Mai no hi pugeu volcats de producció (conté dades de menors i famílies).
--
-- Ús:
--   mysql -u USUARI -p NOM_BD < sql/schema.sql
-- Després creeu el primer gestor amb setup.php (i elimineu-lo).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Usuaris (pares i gestors) ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          varchar(150) NOT NULL,
  `email`         varchar(200) NOT NULL,
  `phone`         varchar(20)  NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `role`          enum('parent','manager') NOT NULL DEFAULT 'parent',
  `created_at`    datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tokens de restabliment de contrasenya (hash SHA-256, 60 min, un sol ús) ──
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at`    datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pr_user` (`user_id`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Setmanes del campus (dilluns–divendres) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `weeks` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`      varchar(150) NOT NULL,
  `start_date` date NOT NULL COMMENT 'Dilluns',
  `end_date`   date NOT NULL COMMENT 'Divendres',
  `active`     tinyint(1) NOT NULL DEFAULT 1,
  `price_club`    decimal(8,2) DEFAULT NULL COMMENT 'Preu de la setmana per a socis (mode weekly)',
  `price_no_club` decimal(8,2) DEFAULT NULL COMMENT 'Preu de la setmana per a no socis (mode weekly)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Inscripcions (una per nen) ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `registrations` (
  `id`                    int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_user_id`        int(10) UNSIGNED NOT NULL,
  `child_first_name`      varchar(100) NOT NULL,
  `child_last_name`       varchar(150) NOT NULL,
  `child_dni`             varchar(20)  NOT NULL,
  `child_birthdate`       date NOT NULL,
  `health_card_number`    varchar(50)  NOT NULL,
  `contact_phone`         varchar(20)  NOT NULL,
  `is_club_player`        tinyint(1) NOT NULL DEFAULT 0,
  `notes`                 text DEFAULT NULL,
  `allow_photos`          tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Autoritza aparició a xarxes socials',
  `allow_data_processing` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Autoritza tractament de dades (obligatori)',
  `status`                enum('draft','confirmed') NOT NULL DEFAULT 'draft',
  `confirmed_at`          datetime DEFAULT NULL,
  `total_amount`          decimal(10,2) DEFAULT NULL,
  `created_at`            datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reg_parent` (`parent_user_id`),
  CONSTRAINT `fk_reg_parent` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Setmanes triades per cada inscripció (modalitat + estat de pagament) ─────
CREATE TABLE IF NOT EXISTS `registration_weeks` (
  `id`              int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_id` int(10) UNSIGNED NOT NULL,
  `week_id`         int(10) UNSIGNED NOT NULL,
  `modality`        enum('8_13','8_15','8_17') NOT NULL,
  `is_paid`         tinyint(1) NOT NULL DEFAULT 0,
  `paid_at`         datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reg_week` (`registration_id`,`week_id`),
  KEY `fk_rw_week` (`week_id`),
  CONSTRAINT `fk_rw_reg`  FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rw_week` FOREIGN KEY (`week_id`)         REFERENCES `weeks` (`id`)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Menú i activitats setmanals (correu dels diumenges 20:00) ────────────────
CREATE TABLE IF NOT EXISTS `weekly_content` (
  `id`             int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `week_id`        int(10) UNSIGNED NOT NULL,
  `menu_dilluns`   text DEFAULT NULL,
  `menu_dimarts`   text DEFAULT NULL,
  `menu_dimecres`  text DEFAULT NULL,
  `menu_dijous`    text DEFAULT NULL,
  `menu_divendres` text DEFAULT NULL,
  `act_dilluns`    text DEFAULT NULL,
  `act_dimarts`    text DEFAULT NULL,
  `act_dimecres`   text DEFAULT NULL,
  `act_dijous`     text DEFAULT NULL,
  `act_divendres`  text DEFAULT NULL,
  `email_sent`     tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = correu de diumenge ja enviat',
  `updated_at`     datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_week` (`week_id`),
  CONSTRAINT `fk_wc_week` FOREIGN KEY (`week_id`) REFERENCES `weeks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contingut editable (WYSIWYG): pàgines i targetes de la portada ───────────
CREATE TABLE IF NOT EXISTS `site_pages` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       varchar(80)  NOT NULL,
  `title`      varchar(200) NOT NULL,
  `content`    longtext NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `homepage_cards` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon`       varchar(50)  NOT NULL DEFAULT '⚽' COMMENT 'Emoji o codi Bootstrap Icon',
  `title`      varchar(150) NOT NULL,
  `content`    text NOT NULL COMMENT 'HTML generat pel WYSIWYG',
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `active`     tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Paràmetres del web (clau/valor) ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_settings` (
  `key`        varchar(80) NOT NULL,
  `value`      text NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Valors per defecte (genèrics, no personals) ──────────────────────────────
INSERT IGNORE INTO `site_settings` (`key`, `value`) VALUES
  ('registration_blocked',         '0'),
  ('registration_blocked_message', 'Les inscripcions estan tancades temporalment.'),
  ('registration_unblock_date',    ''),
  ('og_title',                     'Campus de Futbol'),
  ('og_description',               'Futbol, diversió i aprenentatge per a nens i nenes de 5 a 14 anys.'),
  ('og_image_url',                 ''),
  ('pricing_mode',                 'weekly');

INSERT IGNORE INTO `site_pages` (`slug`, `title`, `content`) VALUES
  ('mes-informacio', 'Més informació', '<p>Editeu aquest contingut des de la zona de gestor.</p>');
