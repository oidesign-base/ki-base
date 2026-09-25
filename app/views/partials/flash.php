<?php
use App\Core\Session;

$types = ['success', 'danger', 'warning', 'info'];
foreach (Session::pullFlash() as $flash):
    $type = in_array($flash['type'], $types, true) ? $flash['type'] : 'info';
?>
<div class="alert alert-<?= $type ?> bg-<?= $type ?>-100 text-<?= $type ?>-600 border-<?= $type ?>-100 px-24 py-11 mb-16 fw-semibold text-md radius-8 d-flex align-items-center justify-content-between gap-3" role="alert">
    <span><?= e($flash['message']) ?></span>
    <button type="button" class="remove-button text-<?= $type ?>-600 text-xxl line-height-1" aria-label="×">
        <iconify-icon icon="iconamoon:sign-times-light" class="icon"></iconify-icon>
    </button>
</div>
<?php endforeach; ?>
