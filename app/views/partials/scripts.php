<?php
/** @var array|null $bundles page asset bundles, e.g. ['datatables'] */
$bundles = $bundles ?? [];
?>
<script src="<?= e(asset('assets/js/lib/jquery-3.7.1.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/lib/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/lib/iconify-icon.min.js')) ?>"></script>
<?php if (in_array('datatables', $bundles, true)): ?>
<script src="<?= e(asset('assets/js/lib/dataTables.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/datatables-init.js')) ?>"></script>
<?php endif; ?>
<script src="<?= e(asset('assets/js/panel.js')) ?>"></script>
