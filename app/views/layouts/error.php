<?php
use App\Core\View;

/**
 * Bare layout for error pages shown to logged-out users or when the
 * database is not available (no sidebar, no user data).
 * @var string      $content
 * @var string|null $title
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="light">
<head>
<?= View::partial('partials/head', ['title' => $title ?? '']) ?>
</head>
<body class="position-relative z-1">
  <img src="<?= e(asset('assets/images/body-bg.png')) ?>" alt="" class="body-bg position-absolute top-0 start-0 h-100 w-100 z-n1">
  <div class="container py-80">
    <?= $content ?>
  </div>
<?= View::partial('partials/scripts') ?>
</body>
</html>
