<?php
/**
 * Esquema de base de datos. Funciona en PostgreSQL (Vercel / Neon), MySQL y SQLite (vista previa).
 * Se ejecuta desde /admin/setup o con: php app/cli.php migrate
 */

function schema_statements(string $driver): array {
    $my = $driver === 'mysql';
    $pg = $driver === 'pgsql';
    $PK   = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : ($pg ? 'SERIAL PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT');
    $LONG = $my ? 'LONGTEXT' : 'TEXT';
    $INT  = $my ? 'INT UNSIGNED' : 'INTEGER';
    $END  = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $IDX  = $my ? 'CREATE INDEX' : 'CREATE INDEX IF NOT EXISTS';

    return [
        "CREATE TABLE IF NOT EXISTS users (
            id $PK,
            name VARCHAR(160) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'equipo',
            client_id $INT NULL,
            last_login " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS settings (
            k VARCHAR(80) NOT NULL PRIMARY KEY,
            v TEXT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS leads (
            id $PK,
            token VARCHAR(40) NOT NULL UNIQUE,
            type VARCHAR(30) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'nuevo',
            name VARCHAR(160) NOT NULL DEFAULT '',
            company VARCHAR(200) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(60) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            rubro VARCHAR(120) NOT NULL DEFAULT '',
            score $INT NULL,
            payload $LONG NULL,
            result $LONG NULL,
            assigned_to $INT NULL,
            ip VARCHAR(64) NOT NULL DEFAULT '',
            source VARCHAR(255) NOT NULL DEFAULT '',
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL,
            updated_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",
        "$IDX idx_leads_type ON leads (type)",
        "$IDX idx_leads_status ON leads (status)",
        "$IDX idx_leads_created ON leads (created_at)",

        "CREATE TABLE IF NOT EXISTS lead_events (
            id $PK,
            lead_id $INT NOT NULL,
            user_id $INT NULL,
            kind VARCHAR(30) NOT NULL,
            body $LONG NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",
        "$IDX idx_events_lead ON lead_events (lead_id)",

        "CREATE TABLE IF NOT EXISTS cases (
            id $PK,
            slug VARCHAR(160) NOT NULL UNIQUE,
            client VARCHAR(160) NOT NULL,
            industry VARCHAR(120) NOT NULL DEFAULT '',
            location VARCHAR(160) NOT NULL DEFAULT '',
            scope VARCHAR(160) NOT NULL DEFAULT '',
            title VARCHAR(255) NOT NULL DEFAULT '',
            subtitle TEXT NULL,
            card_summary TEXT NULL,
            facts $LONG NULL,
            challenge_title VARCHAR(255) NOT NULL DEFAULT '',
            challenge_intro TEXT NULL,
            challenges $LONG NULL,
            work_title VARCHAR(255) NOT NULL DEFAULT '',
            work_intro TEXT NULL,
            services_ops $LONG NULL,
            services_mk $LONG NULL,
            stats $LONG NULL,
            highlight $LONG NULL,
            results_title VARCHAR(255) NOT NULL DEFAULT '',
            results $LONG NULL,
            hero_image VARCHAR(255) NOT NULL DEFAULT '',
            highlight_image VARCHAR(255) NOT NULL DEFAULT '',
            gallery $LONG NULL,
            site_url VARCHAR(255) NOT NULL DEFAULT '',
            published $INT NOT NULL DEFAULT 1,
            sort $INT NOT NULL DEFAULT 0,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL,
            updated_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS reviews (
            id $PK,
            author VARCHAR(160) NOT NULL,
            role VARCHAR(160) NOT NULL DEFAULT '',
            text TEXT NOT NULL,
            rating $INT NOT NULL DEFAULT 5,
            source VARCHAR(40) NOT NULL DEFAULT 'Google',
            case_id $INT NULL,
            featured $INT NOT NULL DEFAULT 1,
            sort $INT NOT NULL DEFAULT 0,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            data TEXT NOT NULL,
            updated_at $INT NOT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS media (
            id $PK,
            name VARCHAR(160) NOT NULL,
            mime VARCHAR(60) NOT NULL,
            size $INT NOT NULL DEFAULT 0,
            data $LONG NOT NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",

        "CREATE TABLE IF NOT EXISTS mail_log (
            id $PK,
            lead_id $INT NULL,
            to_addr TEXT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            ok $INT NOT NULL DEFAULT 0,
            error TEXT NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",

        // ---------------- Propuestas comerciales ----------------
        "CREATE TABLE IF NOT EXISTS proposals (
            id $PK,
            token VARCHAR(40) NOT NULL UNIQUE,
            number VARCHAR(20) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'borrador',
            client_name VARCHAR(200) NOT NULL DEFAULT '',
            client_tagline VARCHAR(255) NOT NULL DEFAULT '',
            contact_name VARCHAR(160) NOT NULL DEFAULT '',
            contact_email VARCHAR(190) NOT NULL DEFAULT '',
            contact_phone VARCHAR(60) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            eyebrow VARCHAR(160) NOT NULL DEFAULT '',
            title VARCHAR(255) NOT NULL DEFAULT '',
            subtitle TEXT NULL,
            blocks $LONG NULL,
            currency VARCHAR(5) NOT NULL DEFAULT 'ARS',
            items $LONG NULL,
            pay_options $LONG NULL,
            terms TEXT NULL,
            valid_until DATE NULL,
            lead_id $INT NULL,
            client_id $INT NULL,
            created_by $INT NULL,
            legacy_url VARCHAR(255) NOT NULL DEFAULT '',
            sent_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NULL,
            viewed_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NULL,
            views $INT NOT NULL DEFAULT 0,
            decided_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NULL,
            approver_name VARCHAR(160) NOT NULL DEFAULT '',
            approver_doc VARCHAR(60) NOT NULL DEFAULT '',
            pay_choice VARCHAR(160) NOT NULL DEFAULT '',
            signature $LONG NULL,
            decision_note TEXT NULL,
            decision_ip VARCHAR(64) NOT NULL DEFAULT '',
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL,
            updated_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",
        "$IDX idx_prop_status ON proposals (status)",

        "CREATE TABLE IF NOT EXISTS proposal_events (
            id $PK,
            proposal_id $INT NOT NULL,
            user_id $INT NULL,
            kind VARCHAR(30) NOT NULL,
            body TEXT NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",
        "$IDX idx_propev ON proposal_events (proposal_id)",

        // ---------------- Clientes ----------------
        "CREATE TABLE IF NOT EXISTS clients (
            id $PK,
            name VARCHAR(200) NOT NULL,
            legal_name VARCHAR(200) NOT NULL DEFAULT '',
            tax_id VARCHAR(40) NOT NULL DEFAULT '',
            industry VARCHAR(120) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            contact_name VARCHAR(160) NOT NULL DEFAULT '',
            contact_email VARCHAR(190) NOT NULL DEFAULT '',
            contact_phone VARCHAR(60) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'activo',
            fee_amount " . ($my ? 'DECIMAL(14,2)' : 'NUMERIC(14,2)') . " NULL,
            fee_currency VARCHAR(5) NOT NULL DEFAULT 'ARS',
            fee_day $INT NULL,
            services TEXT NULL,
            ecosystem $LONG NULL,
            notes TEXT NULL,
            proposal_id $INT NULL,
            created_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL,
            updated_at " . ($pg ? 'TIMESTAMP' : 'DATETIME') . " NOT NULL
        )$END",
    ];
}

function migrate(): array {
    $driver = db()->getAttribute(PDO::ATTR_DRIVER_NAME);
    $done = [];
    foreach (schema_statements($driver) as $sql) {
        try { db()->exec($sql); $done[] = strtok(trim($sql), '('); }
        catch (PDOException $e) {
            // índices ya existentes: se ignoran
            if (!str_contains($e->getMessage(), 'exist') && !str_contains($e->getMessage(), 'Duplicate')) throw $e;
        }
    }
    return $done;
}
