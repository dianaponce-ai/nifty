<?php
/**
 * Utilidades por consola:
 *   php app/cli.php migrate              crea/actualiza tablas
 *   php app/cli.php seed                 carga casos y reseñas iniciales (si están vacías)
 *   php app/cli.php user "Nombre" email clave [admin|equipo]
 */
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/bootstrap.php';
$cmd = $argv[1] ?? '';
if ($cmd === 'migrate') { migrate(); echo "OK: tablas creadas/actualizadas\n"; }
elseif ($cmd === 'seed') { require APP . '/seed.php'; seed_content(); echo "OK: contenido inicial cargado\n"; }
elseif ($cmd === 'user' && isset($argv[4])) {
    insert('users', ['name' => $argv[2], 'email' => strtolower($argv[3]), 'password_hash' => password_hash($argv[4], PASSWORD_DEFAULT), 'role' => $argv[5] ?? 'admin', 'created_at' => now()]);
    echo "OK: usuario creado\n";
} else echo "Uso: php app/cli.php migrate | seed | user \"Nombre\" email clave [admin|equipo]\n";
