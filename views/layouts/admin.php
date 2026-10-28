<?php
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$on = fn(string $p) => ($p === '/admin' ? $path === '/admin' : str_starts_with($path, $p)) ? ' class="on"' : '';
$newLeads = (int) val("SELECT COUNT(*) FROM leads WHERE status = 'nuevo'");
$fl = flash();
?><!DOCTYPE html>
<html lang="es-AR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Panel') . ' — Nifty Backend') ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="adm">
<aside class="adm-side" id="admSide">
  <a class="adm-brand" href="/admin"><img src="<?= e(logo_url()) ?>" alt="Nifty"><span>Backend</span></a>
  <nav>
    <p class="adm-sec">CRM</p>
    <a href="/admin"<?= $on('/admin') ?>><?= icon('chart', 18) ?>Dashboard</a>
    <a href="/admin/leads"<?= $on('/admin/leads') ?>><?= icon('users', 18) ?>Leads<?php if ($newLeads): ?><em class="badge"><?= $newLeads ?></em><?php endif; ?></a>
    <a href="/admin/propuestas"<?= $on('/admin/propuestas') ?>><?= icon('receipt', 18) ?>Propuestas</a>
    <a href="/admin/clientes"<?= $on('/admin/clientes') ?>><?= icon('briefcase', 18) ?>Clientes</a>
    <span class="adm-soon"><?= icon('chat', 18) ?>Tickets <small>Fase 3</small></span>
    <p class="adm-sec">Sitio</p>
    <a href="/admin/casos"<?= $on('/admin/casos') ?>><?= icon('star', 18) ?>Casos de éxito</a>
    <a href="/admin/resenas"<?= $on('/admin/resenas') ?>><?= icon('chat', 18) ?>Reseñas</a>
    <?php if (($me['role'] ?? '') === 'admin'): ?>
    <p class="adm-sec">Configuración</p>
    <a href="/admin/ajustes"<?= $on('/admin/ajustes') ?>><?= icon('gear', 18) ?>Ajustes</a>
    <a href="/admin/usuarios"<?= $on('/admin/usuarios') ?>><?= icon('lock', 18) ?>Usuarios</a>
    <?php endif; ?>
  </nav>
  <div class="adm-user">
    <div><b><?= e($me['name'] ?? '') ?></b><span><?= e($me['email'] ?? '') ?></span></div>
    <a href="/" target="_blank">Ver sitio ↗</a> · <a href="/admin/logout">Salir</a>
  </div>
</aside>
<div class="adm-main">
  <header class="adm-top">
    <button class="adm-burger" type="button" onclick="document.getElementById('admSide').classList.toggle('open')" aria-label="Menú">☰</button>
    <h1><?= e($title ?? 'Panel') ?></h1>
    <div class="adm-top-actions"><?= $actions ?? '' ?></div>
  </header>
  <?php if ($fl): ?><div class="adm-flash <?= e($fl[0]) ?>"><?= e($fl[1]) ?></div><?php endif; ?>
  <div class="adm-content"><?= $content ?></div>
</div>
<script src="<?= asset('assets/js/admin.js') ?>" defer></script>
<script src="<?= asset('assets/js/password-toggle.js') ?>" defer></script>
</body>
</html>
