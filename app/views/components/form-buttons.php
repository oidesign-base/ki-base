<?php
/**
 * @var string      $backUrl
 * @var string|null $submitLabel  text of the submit button (default: "save")
 */
?>
<div class="d-flex align-items-center justify-content-center gap-3 mt-24">
    <a href="<?= e($backUrl) ?>" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-sm px-56 py-11 radius-8">
        <?= e(t('action.cancel')) ?>
    </a>
    <button type="submit" class="btn btn-primary border border-primary-600 text-sm px-56 py-12 radius-8">
        <?= e($submitLabel ?? t('action.save')) ?>
    </button>
</div>
