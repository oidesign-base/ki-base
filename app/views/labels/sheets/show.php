<?php
use App\Core\LabelCode;
use App\Core\View;

/**
 * @var array $sheet
 * @var array $codes  code, status, status_changed_at, item_id
 */
$statusClass = [
    'free'     => 'bg-success-focus text-success-600 border border-success-main',
    'assigned' => 'bg-info-focus text-info-600 border border-info-main',
    'spoiled'  => 'bg-danger-focus text-danger-600 border border-danger-main',
];
?>
<div class="card p-0 radius-12 mb-24">
    <div class="card-body p-24 d-flex flex-wrap gap-4">
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('prefixes.prefix')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e($sheet['prefix']) ?> — <?= e($sheet['description']) ?></div>
        </div>
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('sheets.codes')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e(LabelCode::format($sheet['first_code'])) ?> – <?= e(LabelCode::format($sheet['last_code'])) ?></div>
        </div>
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('sheets.printed')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e(format_datetime($sheet['printed_at'])) ?><?= $sheet['printed_by_name'] !== null ? ' · ' . e($sheet['printed_by_name']) : '' ?></div>
        </div>
    </div>
</div>

<div class="card basic-data-table h-100 p-0 radius-12">
    <?= View::partial('components/list-toolbar', [
        'filters' => [
            ['type' => 'check', 'column' => '.col-status', 'value' => 'free',     'label' => t('sheets.free'),     'checked' => true],
            ['type' => 'check', 'column' => '.col-status', 'value' => 'assigned', 'label' => t('sheets.assigned'), 'checked' => true],
            ['type' => 'check', 'column' => '.col-status', 'value' => 'spoiled',  'label' => t('sheets.spoiled_count'),  'checked' => true],
        ],
        'buttons' => [
            ['url' => url('/labels/pdf') . '?ids=' . (int) $sheet['id'], 'icon' => 'ph-printer', 'label' => t('sheets.reprint'), 'target' => '_blank'],
        ],
    ]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0 w-100" data-datatable data-copy>
                <thead>
                    <tr>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.position')) ?></th>
                        <th scope="col"><?= e(t('sheets.code')) ?></th>
                        <th scope="col" class="col-status text-center"><?= e(t('field.status')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $n => $c): ?>
                    <tr>
                        <td class="text-center"><?= $n + 1 ?></td>
                        <td class="text-nowrap" data-copy-value="<?= e($c['code']) ?>" data-search="<?= e($c['code'] . ' ' . LabelCode::format($c['code'])) ?>">
                            <span class="fw-semibold text-primary-light"><?= e(LabelCode::format($c['code'])) ?></span>
                        </td>
                        <td class="text-center" data-no-copy data-search="<?= e($c['status']) ?>">
                            <span class="<?= $statusClass[$c['status']] ?> px-24 py-4 radius-4 fw-medium text-sm text-nowrap"><?= e(t('sheets.status.' . $c['status'])) ?></span>
                        </td>
                        <td class="text-center" data-no-copy>
                            <?php if ($c['status'] !== 'assigned'): ?>
                            <form method="post" action="<?= e(url('/labels/' . $sheet['id'] . '/spoil')) ?>" class="m-0 d-flex justify-content-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="code" value="<?= e($c['code']) ?>">
                                <?php if ($c['status'] === 'free'): ?>
                                <button type="submit" title="<?= e(t('sheets.mark_spoiled')) ?>" aria-label="<?= e(t('sheets.mark_spoiled')) ?>"
                                        class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                    <i class="ph ph-prohibit" aria-hidden="true"></i>
                                </button>
                                <?php else: ?>
                                <button type="submit" title="<?= e(t('sheets.mark_free')) ?>" aria-label="<?= e(t('sheets.mark_free')) ?>"
                                        class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                    <i class="ph ph-arrow-counter-clockwise" aria-hidden="true"></i>
                                </button>
                                <?php endif; ?>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
