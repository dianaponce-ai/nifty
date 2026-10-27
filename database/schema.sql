-- Nifty Agency — esquema MySQL (alternativa a /admin/setup)
-- Importar en phpMyAdmin si preferís crear las tablas a mano.

CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(160) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'equipo',
            client_id INT UNSIGNED NULL,
            last_login DATETIME NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
            k VARCHAR(80) NOT NULL PRIMARY KEY,
            v TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(40) NOT NULL UNIQUE,
            type VARCHAR(30) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'nuevo',
            name VARCHAR(160) NOT NULL DEFAULT '',
            company VARCHAR(200) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(60) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            rubro VARCHAR(120) NOT NULL DEFAULT '',
            score INT UNSIGNED NULL,
            payload LONGTEXT NULL,
            result LONGTEXT NULL,
            assigned_to INT UNSIGNED NULL,
            ip VARCHAR(64) NOT NULL DEFAULT '',
            source VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_leads_type ON leads (type);

CREATE INDEX idx_leads_status ON leads (status);

CREATE INDEX idx_leads_created ON leads (created_at);

CREATE TABLE IF NOT EXISTS lead_events (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            lead_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NULL,
            kind VARCHAR(30) NOT NULL,
            body LONGTEXT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_events_lead ON lead_events (lead_id);

CREATE TABLE IF NOT EXISTS cases (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL UNIQUE,
            client VARCHAR(160) NOT NULL,
            industry VARCHAR(120) NOT NULL DEFAULT '',
            location VARCHAR(160) NOT NULL DEFAULT '',
            scope VARCHAR(160) NOT NULL DEFAULT '',
            title VARCHAR(255) NOT NULL DEFAULT '',
            subtitle TEXT NULL,
            card_summary TEXT NULL,
            facts LONGTEXT NULL,
            challenge_title VARCHAR(255) NOT NULL DEFAULT '',
            challenge_intro TEXT NULL,
            challenges LONGTEXT NULL,
            work_title VARCHAR(255) NOT NULL DEFAULT '',
            work_intro TEXT NULL,
            services_ops LONGTEXT NULL,
            services_mk LONGTEXT NULL,
            stats LONGTEXT NULL,
            highlight LONGTEXT NULL,
            results_title VARCHAR(255) NOT NULL DEFAULT '',
            results LONGTEXT NULL,
            hero_image VARCHAR(255) NOT NULL DEFAULT '',
            highlight_image VARCHAR(255) NOT NULL DEFAULT '',
            gallery LONGTEXT NULL,
            site_url VARCHAR(255) NOT NULL DEFAULT '',
            published INT UNSIGNED NOT NULL DEFAULT 1,
            sort INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            author VARCHAR(160) NOT NULL,
            role VARCHAR(160) NOT NULL DEFAULT '',
            text TEXT NOT NULL,
            rating INT UNSIGNED NOT NULL DEFAULT 5,
            source VARCHAR(40) NOT NULL DEFAULT 'Google',
            case_id INT UNSIGNED NULL,
            featured INT UNSIGNED NOT NULL DEFAULT 1,
            sort INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mail_log (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            lead_id INT UNSIGNED NULL,
            to_addr TEXT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            ok INT UNSIGNED NOT NULL DEFAULT 0,
            error TEXT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

