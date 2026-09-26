<?php
use App\Core\LabelCode;
use App\Core\View;

/**
 * A generation of codes: its sheets as 6 x 8 grids, like the printed paper
 * (the first code is top left). Search and the status filters in the card
 * header hide non-matching labels but keep the others in their place.
 *
 * @var array $generation
 * @var array $sheets  id, first_code, last_code
 * @var array $codes   sheet id => list of [code, status]
 */
$cellClass = [
    'free'     => 'bg-success-focus border-success-main',
    'assigned' => 'bg-info-focus border-info-main',
    'spoiled'  => 'bg-danger-focus border-danger-main',
];
$statusText = [
    'free'     => 'text-success-600',
    'assigned' => 'text-info-600',
    'spoiled'  => 'text-danger-600',
];
$allIds = implode(',', array_map(static fn (array $s): int => (int) $s['id'], $sheets));
$total  = count($sheets);
?>
<div class="card p-0 radius-12 mb-24">
    <div class="card-body p-24 d-flex flex-wrap gap-4">
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('prefixes.prefix')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e($generation['prefix']) ?> — <?= e($generation['description']) ?></div>
        </div>
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('sheets.codes')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e(LabelCode::format($generation['first_code'])) ?> – <?= e(LabelCode::format($generation['last_code'])) ?></div>
        </div>
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('sheets.sheets_count')) ?></div>
            <div class="fw-semibold text-primary-light"><?= $total ?> × 48</div>
        </div>
        <div>
            <div class="text-sm text-secondary-light"><?= e(t('sheets.printed')) ?></div>
            <div class="fw-semibold text-primary-light"><?= e(format_datetime($generation['created_at'])) ?><?= $generation['created_by_name'] !== null ? ' · ' . e($generation['created_by_name']) : '' ?></div>
        </div>
    </div>
</div>

<div class="card basic-data-table p-0 radius-12">
    <?= View::partial('components/list-toolbar', [
        'filters' => [
            ['type' => 'check', 'column' => 'status', 'value' => 'free',     'label' => t('sheets.free'),          'checked' => true],
            ['type' => 'check', 'column' => 'status', 'value' => 'assigned', 'label' => t('sheets.assigned'),      'checked' => true],
            ['type' => 'check', 'column' => 'status', 'value' => 'spoiled',  'label' => t('sheets.spoiled_count'), 'checked' => true],
        ],
        'buttons' => [
            ['url' => url('/labels/pdf') . '?ids=' . $allIds, 'icon' => 'ph-printer', 'label' => t('sheets.print_all'), 'target' => '_blank'],
        ],
    ]) ?>
    <div class="card-body p-24 code-sheets" data-filter-grid>
        <?php foreach ($sheets as $n => $sheet): ?>
        <section class="code-sheet" id="sheet-<?= (int) $sheet['id'] ?>" data-grid-section>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                <h2 class="text-md fw-semibold text-primary-light mb-0">
                    <?= e(t('sheets.sheet_of', ['n' => $n + 1, 'total' => $total])) ?>
                    <span class="fw-normal text-secondary-light text-sm ms-8"><?= e(LabelCode::format($sheet['first_code'])) ?> – <?= e(LabelCode::format($sheet['last_code'])) ?></span>
                </h2>
                <a href="<?= e(url('/labels/pdf') . '?ids=' . (int) $sheet['id']) ?>" target="_blank" rel="noopener"
                   title="<?= e(t('sheets.reprint')) ?>" aria-label="<?= e(t('sheets.reprint')) ?>" data-bs-toggle="tooltip"
                   class="w-32-px h-32-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="ph ph-printer" aria-hidden="true"></i>
                </a>
            </div>
            <div class="code-grid">
                <?php foreach ($codes[$sheet['id']] ?? [] as $c): $status = $c['status']; ?>
                <div class="code-cell border <?= $cellClass[$status] ?>" data-grid-cell data-status="<?= e($status) ?>"
                     data-search="<?= e(strtolower($c['code'] . ' ' . LabelCode::format($c['code']))) ?>">
                    <div class="code-cell__text">
                        <div class="code-cell__code fw-semibold text-primary-light"><?= e(LabelCode::format($c['code'])) ?></div>
                        <div class="text-xs fw-medium <?= $statusText[$status] ?>"><?= e(t('sheets.status.' . $status)) ?></div>
                    </div>
                    <?php if ($status !== 'assigned'): ?>
                    <form method="post" action="<?= e(url('/labels/' . $generation['id'] . '/spoil')) ?>" class="m-0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="code" value="<?= e($c['code']) ?>">
                        <?php if ($status === 'free'): ?>
                        <button type="submit" title="<?= e(t('sheets.mark_spoiled')) ?>" aria-label="<?= e(t('sheets.mark_spoiled')) ?>"
                                class="code-cell__btn bg-base text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                            <i class="ph ph-prohibit" aria-hidden="true"></i>
                        </button>
                        <?php else: ?>
                        <button type="submit" title="<?= e(t('sheets.mark_free')) ?>" aria-label="<?= e(t('sheets.mark_free')) ?>"
                                class="code-cell__btn bg-base text-success-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                            <i class="ph ph-arrow-counter-clockwise" aria-hidden="true"></i>
                        </button>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
        <p class="text-secondary-light mb-0 mt-20" data-grid-info
           data-text-all="<?= e(t('list.count')) ?>" data-text-filtered="<?= e(t('list.count_filtered')) ?>"
           data-text-none="<?= e(t('list.none_found')) ?>"></p>
    </div>
</div>
