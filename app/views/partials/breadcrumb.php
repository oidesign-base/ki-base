<?php
/**
 * @var string      $title
 * @var string|null $activePath
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <h1 class="h6 fw-semibold mb-0"><?= e($title ?? '') ?></h1>
  <?php if (($activePath ?? '/') !== '/'): ?>
  <ul class="d-flex align-items-center gap-2">
    <li class="fw-medium">
      <a href="<?= e(url('/')) ?>" class="d-flex align-items-center gap-1 hover-text-primary">
        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
        <?= e(t('layout.home')) ?>
      </a>
    </li>
    <li>-</li>
    <li class="fw-medium"><?= e($title ?? '') ?></li>
  </ul>
  <?php endif; ?>
</div>
