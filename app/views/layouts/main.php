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
<html lang="pl" data-theme="light">
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

  <button type="button" class="go-top btn btn-primary w-44-px h-44-px p-0 radius-8 d-flex align-items-center justify-content-center"
          data-go-top aria-label="<?= e(t('layout.go_top')) ?>" title="<?= e(t('layout.go_top')) ?>">
    <i class="ph-bold ph-arrow-up text-xl" aria-hidden="true"></i>
  </button>

<?= View::partial('partials/scripts', ['bundles' => $scripts ?? []]) ?>
</body>
</html>
