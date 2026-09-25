<?php
use App\Core\View;

/**
 * Main panel layout (logged-in pages).
 * @var string      $content
 * @var string|null $title
 * @var string|null $activePath
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="light">
<head>
<?= View::partial('partials/head', ['title' => $title ?? '']) ?>
</head>
<body class="position-relative z-1">
  <img src="<?= e(asset('assets/images/body-bg.png')) ?>" alt="" class="body-bg position-absolute top-0 start-0 h-100 w-100 z-n1">

  <?= View::partial('partials/sidebar', ['activePath' => $activePath ?? '']) ?>

  <main class="dashboard-main">
    <?= View::partial('partials/nav') ?>

    <div class="dashboard-main-body">
      <?= View::partial('partials/breadcrumb', ['title' => $title ?? '', 'activePath' => $activePath ?? '']) ?>
      <?= View::partial('partials/flash') ?>
      <?= $content ?>
    </div>

    <?= View::partial('partials/footer') ?>
  </main>

<?= View::partial('partials/scripts') ?>
</body>
</html>
