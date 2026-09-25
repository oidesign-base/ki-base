<?php /** @var bool $active */ ?>
<?php if ($active): ?>
<span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm"><?= e(t('status.active')) ?></span>
<?php else: ?>
<span class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm"><?= e(t('status.inactive')) ?></span>
<?php endif; ?>
