<?php
use App\Core\View;

/** @var array $categories */
?>
<div class="card basic-data-table h-100 p-0 radius-12">
    <?= View::partial('components/list-toolbar', [
        'addUrl'   => url('/settings/categories/create'),
        'addLabel' => t('categories.add'),
        'filters'  => [
            ['type' => 'check', 'column' => '.col-status', 'value' => 'active',   'label' => t('filter.active'),   'checked' => true],
            ['type' => 'check', 'column' => '.col-status', 'value' => 'inactive', 'label' => t('filter.inactive'), 'checked' => true],
        ],
    ]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0 w-100" data-datatable data-copy>
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('categories.name')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('categories.products_count')) ?></th>
                        <th scope="col" class="col-status text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><span class="fw-medium text-secondary-light"><?= e($c['name']) ?></span></td>
                        <td class="text-center"><?= (int) $c['products_count'] ?></td>
                        <td class="text-center" data-no-copy data-order="<?= (int) $c['is_active'] ?>" data-search="<?= (int) $c['is_active'] === 1 ? 'active' : 'inactive' ?>"><?= View::partial('components/status-badge', ['active' => (int) $c['is_active'] === 1]) ?></td>
                        <td class="text-center" data-no-copy>
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
