-- ============================================================
-- Schéma additionnel pour l'interface d'administration RC Slopes
-- À exécuter APRÈS l'import du dump rcslopes_2026-06-25.sql
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Table des administrateurs
-- ------------------------------------------------------------
-- Niveau de privilège :
--   'editor' : peut créer / modifier / supprimer les données des tables (slopes, weather_forecast, wind_station) + gérer les images
--   'admin'  : éditor + peut gérer les autres administrateurs (créer / modifier / supprimer des comptes admin)
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `administrators` (
  `admin_id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('editor','admin') NOT NULL DEFAULT 'editor' COMMENT 'editor = CRUD données / admin = CRUD données + gestion des administrateurs',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table des tentatives de connexion (protection brute-force basique)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `attempt_id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_email_time` (`email`,`attempted_at`),
  KEY `idx_ip_time` (`ip_address`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Compte administrateur par défaut
-- Email     : admin@rcslopes.local
-- Mot de passe : ChangeMoi123!  (À CHANGER IMMÉDIATEMENT après la première connexion)
-- Le hash ci-dessous correspond à ce mot de passe (bcrypt / password_hash PHP)
-- ------------------------------------------------------------
INSERT INTO `administrators` (`email`, `password_hash`, `full_name`, `role`, `is_active`)
VALUES (
  'admin@rcslopes.local',
  '$2y$10$wH8aQKxN3pYJZmF5rT9vBuZ8oQeR2KqYx7vL1nC4dS6jM0pXwE3Ga',
  'Administrateur Principal',
  'admin',
  1
);


eric.collard@free.fr
regfeq-5baCky-pucnur

-- IMPORTANT : Le hash ci-dessus est un PLACEHOLDER.
-- Lance le script /admin/install.php (fourni) une seule fois après le déploiement :
-- il régénère un hash correct pour le mot de passe ChangeMoi123! avec la version
-- exacte de PHP/bcrypt de ton serveur, et te demande de le changer immédiatement.
