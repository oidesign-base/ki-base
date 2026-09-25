<?php
use App\Core\View;

/**
 * Main panel layout (logged-in pages).
 * @var string      $content
 * @var string|null $title
 * @var string|null $activePath
 * @var array|null  $breadcrumbs
 * @var array|null  $scripts     asset bundles for this page, e.g. ['datatables']
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="light">
<head>
<?= View::partial('partials/head', ['title' => $title ?? '', 'bundles' => $scripts ?? []]) ?>
</head>
<body class="position-relative z-1" data-i18n-copy="<?= e(t('action.copy')) ?>" data-i18n-copied="<?= e(t('action.copied')) ?>">
  <img src="<?= e(asset('assets/images/body-bg.png')) ?>" alt="" class="body-bg position-absolute top-0 start-0 h-100 w-100 z-n1">

  <?= View::partial('partials/sidebar', ['activePath' => $activePath ?? '']) ?>

  <main class="dashboard-main">
    <?= View::partial('partials/nav', ['title' => $title ?? '', 'activePath' => $activePath ?? '', 'breadcrumbs' => $breadcrumbs ?? []]) ?>

    <div class="dashboard-main-body">
      <?= View::partial('partials/flash') ?>
      <?= $content ?>
    </div>

  </main>

<?= View::partial('partials/scripts', ['bundles' => $scripts ?? []]) ?>
</body>
</html>
