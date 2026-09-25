<?php
use App\Core\Validate;
use App\Core\View;

/** @var array $suppliers */
?>
<div class="card h-100 p-0 radius-12">
    <?= View::partial('components/list-card-header', ['addUrl' => url('/settings/suppliers/create'), 'addLabel' => t('suppliers.add')]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0" data-datatable>
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('suppliers.name')) ?></th>
                        <th scope="col"><?= e(t('suppliers.tax_id')) ?></th>
                        <th scope="col"><?= e(t('suppliers.contact')) ?></th>
                        <th scope="col" class="text-center"><?= e(t('suppliers.batches_count')) ?></th>
                        <th scope="col" class="text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><span class="text-md fw-medium text-secondary-light"><?= e($s['name']) ?></span></td>
                        <td class="text-nowrap"><?= e(Validate::formatNip($s['tax_id'])) ?></td>
                        <td><?= e($s['contact'] ?? '') ?></td>
                        <td class="text-center"><?= (int) $s['batches_count'] ?></td>
                        <td class="text-center" data-order="<?= (int) $s['is_active'] ?>"><?= View::partial('components/status-badge', ['active' => (int) $s['is_active'] === 1]) ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <?= View::partial('components/row-actions', [
                                    'editUrl'   => url('/settings/suppliers/' . $s['id'] . '/edit'),
                                    'toggleUrl' => url('/settings/suppliers/' . $s['id'] . '/toggle'),
                                    'active'    => (int) $s['is_active'] === 1,
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
