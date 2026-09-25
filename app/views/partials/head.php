<?php /** @var string|null $title */ ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e(($title ?? '') !== '' ? $title . ' — ' . config('app.name') : config('app.name')) ?></title>
<link rel="icon" type="image/png" href="<?= e(asset('assets/images/logo-icon.png')) ?>">
<script>
  // Apply the saved light/dark theme before the page is painted (no flash).
  (function () {
    try {
      var theme = localStorage.getItem('theme');
      if (theme === 'dark' || theme === 'light') {
        document.documentElement.setAttribute('data-theme', theme);
      }
    } catch (e) {}
  })();
</script>
<link rel="stylesheet" href="<?= e(asset('assets/css/remixicon.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/phosphor/regular/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/phosphor/bold/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/lib/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/panel.css')) ?>">
