<?php
/**
 * Copiá este archivo como config.php (NO se sube a GitHub) y completá los datos.
 */
return [
    // URL pública sin barra final
    'base_url' => 'https://nifty-agency.com',

    // Base de datos MySQL de Hostinger
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=u000000000_nifty;charset=utf8mb4',
        'user' => 'u000000000_nifty',
        'pass' => 'CAMBIAR',
    ],

    // SMTP de Google Workspace (usar una contraseña de aplicación)
    'smtp' => [
        'host'      => 'smtp.gmail.com',
        'port'      => 587,
        'secure'    => 'tls',
        'user'      => 'hola@nifty-agency.com',
        'pass'      => 'CONTRASEÑA_DE_APLICACION',
        'from'      => 'hola@nifty-agency.com',
        'from_name' => 'Nifty Agency',
    ],

    // Opcional: API de Claude para generar textos de casos y lecturas de diagnósticos
    'anthropic' => [
        'api_key' => '',
        'model'   => 'claude-sonnet-5-5',
    ],

    // Clave para firmar tokens y sesiones (poné una cadena larga y aleatoria)
    'app_key' => 'CAMBIAR-POR-UNA-CADENA-LARGA-ALEATORIA',

    'debug' => false,
];
