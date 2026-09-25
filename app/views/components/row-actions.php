<?php
/**
 * Edit link + on/off toggle for a list row.
 * @var string $editUrl
 * @var string $toggleUrl
 * @var bool   $active
 */
?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= e($editUrl) ?>" title="<?= e(t('action.edit')) ?>" aria-label="<?= e(t('action.edit')) ?>"
       class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
        <iconify-icon icon="lucide:edit"></iconify-icon>
    </a>
    <form method="post" action="<?= e($toggleUrl) ?>" class="m-0">
        <?= csrf_field() ?>
        <?php if ($active): ?>
        <button type="submit" title="<?= e(t('action.disable')) ?>" aria-label="<?= e(t('action.disable')) ?>"
                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
            <iconify-icon icon="mingcute:forbid-circle-line"></iconify-icon>
        </button>
        <?php else: ?>
        <button type="submit" title="<?= e(t('action.enable')) ?>" aria-label="<?= e(t('action.enable')) ?>"
                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center border-0">
            <iconify-icon icon="mingcute:check-circle-line"></iconify-icon>
        </button>
        <?php endif; ?>
    </form>
</div>
