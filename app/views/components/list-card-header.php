<?php
/**
 * Card header of a list page with the "add" button on the right.
 * @var string $addUrl
 * @var string $addLabel
 */
?>
<div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-end">
    <a href="<?= e($addUrl) ?>" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
        <?= e($addLabel) ?>
    </a>
</div>
