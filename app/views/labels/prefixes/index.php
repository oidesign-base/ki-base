<?php
use App\Core\View;

/** @var array $prefixes */
?>
<div class="card basic-data-table h-100 p-0 radius-12">
    <?= View::partial('components/list-toolbar', [
        'addUrl'   => url('/labels/prefixes/create'),
        'addLabel' => t('prefixes.add'),
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
                        <th scope="col" class="col-flag"><?= e(t('prefixes.prefix')) ?></th>
                        <th scope="col"><?= e(t('prefixes.description')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('prefixes.codes_count')) ?></th>
                        <th scope="col" class="col-flag text-center"><?= e(t('prefixes.default')) ?></th>
                        <th scope="col" class="col-status text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prefixes as $p): $active = (int) $p['is_active'] === 1; ?>
                    <tr>
                        <td><span class="fw-semibold text-primary-light"><?= e($p['prefix']) ?></span></td>
                        <td><?= e($p['description']) ?></td>
                        <td class="text-center"><?= (int) $p['codes_count'] ?></td>
                        <td class="text-center" data-no-copy data-order="<?= (int) $p['is_default'] ?>">
                            <?php if ((int) $p['is_default'] === 1): ?>
                            <span class="bg-info-focus text-info-600 border border-info-main px-24 py-4 radius-4 fw-medium text-sm"><?= e(t('prefixes.default')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center" data-no-copy data-order="<?= (int) $p['is_active'] ?>" data-search="<?= $active ? 'active' : 'inactive' ?>"><?= View::partial('components/status-badge', ['active' => $active]) ?></td>
                        <td class="text-center" data-no-copy>
                            <div class="d-flex justify-content-center">
                                <?= View::partial('components/row-actions', [
                                    'editUrl'   => url('/labels/prefixes/' . $p['id'] . '/edit'),
                                    'toggleUrl' => url('/labels/prefixes/' . $p['id'] . '/toggle'),
                                    'active'    => $active,
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
