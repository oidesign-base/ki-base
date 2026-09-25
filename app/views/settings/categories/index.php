<?php
use App\Core\View;

/** @var array $categories */
?>
<div class="card h-100 p-0 radius-12">
    <?= View::partial('components/list-card-header', ['addUrl' => url('/settings/categories/create'), 'addLabel' => t('categories.add')]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0" data-datatable>
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('categories.name')) ?></th>
                        <th scope="col" class="text-center"><?= e(t('categories.products_count')) ?></th>
                        <th scope="col" class="text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><span class="text-md fw-medium text-secondary-light"><?= e($c['name']) ?></span></td>
                        <td class="text-center"><?= (int) $c['products_count'] ?></td>
                        <td class="text-center" data-order="<?= (int) $c['is_active'] ?>"><?= View::partial('components/status-badge', ['active' => (int) $c['is_active'] === 1]) ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <?= View::partial('components/row-actions', [
                                    'editUrl'   => url('/settings/categories/' . $c['id'] . '/edit'),
                                    'toggleUrl' => url('/settings/categories/' . $c['id'] . '/toggle'),
                                    'active'    => (int) $c['is_active'] === 1,
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
