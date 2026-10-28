<!DOCTYPE html>
<html lang="es-AR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title'] ?? 'Nifty') ?></title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="adm-bare">
<div class="auth-card">
  <img src="<?= e(logo_url()) ?>" alt="Nifty Agency" class="auth-logo">
  <?= $content ?>
</div>
</body>
</html>
