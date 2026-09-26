<?php
use App\Core\Validate;
use App\Core\View;

/** @var array $suppliers */
?>
<div class="card basic-data-table h-100 p-0 radius-12">
    <?= View::partial('components/list-toolbar', ['addUrl' => url('/settings/suppliers/create'), 'addLabel' => t('suppliers.add')]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0 w-100" data-datatable data-copy>
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('suppliers.name')) ?></th>
                        <th scope="col" class="col-nip"><?= e(t('suppliers.tax_id')) ?></th>
                        <th scope="col" class="col-phone"><?= e(t('suppliers.phone')) ?></th>
                        <th scope="col"><?= e(t('suppliers.email')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('suppliers.batches_count')) ?></th>
                        <th scope="col" class="col-status text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><span class="fw-medium text-secondary-light"><?= e($s['name']) ?></span></td>
                        <td class="text-nowrap"><?= e(Validate::formatNip($s['tax_id'])) ?></td>
                        <td class="text-nowrap"><?= e($s['phone'] ?? '') ?></td>
                        <td><?= e($s['email'] ?? '') ?></td>
                        <td class="text-center"><?= (int) $s['batches_count'] ?></td>
                        <td class="text-center" data-no-copy data-order="<?= (int) $s['is_active'] ?>" data-search="<?= (int) $s['is_active'] === 1 ? 'active' : 'inactive' ?>"><?= View::partial('components/status-badge', ['active' => (int) $s['is_active'] === 1]) ?></td>
                        <td class="text-center" data-no-copy>
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
