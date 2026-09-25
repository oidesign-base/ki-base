<?php
/**
 * Top bar: only the breadcrumb trail. The last element is the page heading.
 *
 * @var string      $title
 * @var string|null $activePath
 * @var array|null  $breadcrumbs  intermediate links: [[label, url], ...]
 */
$trail = $breadcrumbs ?? [];
?>
<div class="navbar-header panel-topbar">
  <nav aria-label="<?= e(t('layout.breadcrumb')) ?>" class="h-100 d-flex align-items-center">
    <ol class="panel-breadcrumb d-flex flex-wrap align-items-center gap-2 mb-0 p-0 h6 fw-semibold">
      <li class="d-flex">
        <a href="<?= e(url('/')) ?>" class="d-flex hover-text-primary" aria-label="<?= e(t('menu.dashboard')) ?>" title="<?= e(t('menu.dashboard')) ?>">
          <i class="ph ph-house-simple"></i>
        </a>
      </li>
      <?php foreach ($trail as [$label, $href]): ?>
      <li class="panel-breadcrumb__sep" aria-hidden="true">-</li>
      <li><a href="<?= e($href) ?>" class="hover-text-primary"><?= e($label) ?></a></li>
      <?php endforeach; ?>
      <li class="panel-breadcrumb__sep" aria-hidden="true">-</li>
      <li><h1 class="h6 fw-semibold mb-0 d-inline"><?= e($title ?? '') ?></h1></li>
    </ol>
  </nav>
</div>
