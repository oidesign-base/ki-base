<?php /** @var string $backUrl */ ?>
<div class="d-flex align-items-center justify-content-center gap-3 mt-24">
    <a href="<?= e($backUrl) ?>" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
        <?= e(t('action.cancel')) ?>
    </a>
    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
        <?= e(t('action.save')) ?>
    </button>
</div>
