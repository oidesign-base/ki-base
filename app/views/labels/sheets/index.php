<?php
use App\Core\LabelCode;
use App\Core\View;

/**
 * @var array      $sheets
 * @var array      $prefixes  prefixes that have sheets (for the filter)
 * @var array|null $download  just generated: ['ids' => [...], 'range' => '…']
 */
$filters = [];
if (count($prefixes) > 1) {
    $filters[] = ['type' => 'select', 'column' => '.col-prefix', 'label' => t('sheets.all_prefixes'),
                  'options' => array_combine($prefixes, $prefixes)];
}
?>
<?php if ($download !== null): ?>
<div class="alert bg-primary-50 text-primary-600 border border-primary-600 px-24 py-16 mb-16 radius-8 d-flex flex-wrap align-items-center justify-content-between gap-3" role="status">
    <div>
        <div class="fw-semibold text-md"><?= e(t('sheets.ready', ['range' => $download['range']])) ?></div>
        <div class="text-sm text-secondary-light mt-4"><?= e(t('sheets.print_hint')) ?></div>
    </div>
    <a href="<?= e(url('/labels/pdf') . '?ids=' . implode(',', array_map('intval', $download['ids']))) ?>" target="_blank" rel="noopener"
       class="btn btn-primary-600 text-sm px-20 h-40-px radius-8 d-inline-flex align-items-center gap-2">
        <i class="ph ph-file-pdf text-xl" aria-hidden="true"></i><?= e(t('sheets.download_pdf')) ?>
    </a>
</div>
<?php endif; ?>

<div class="card basic-data-table h-100 p-0 radius-12">
    <?= View::partial('components/list-toolbar', [
        'addUrl'   => url('/labels/create'),
        'addLabel' => t('sheets.new'),
        'filters'  => $filters,
    ]) ?>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0 w-100" data-datatable data-copy>
                <thead>
                    <tr>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.number')) ?></th>
                        <th scope="col" class="col-prefix"><?= e(t('prefixes.prefix')) ?></th>
                        <th scope="col"><?= e(t('sheets.codes')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.free')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.assigned')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.spoiled_count')) ?></th>
                        <th scope="col"><?= e(t('sheets.printed')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sheets as $s): ?>
                    <tr>
                        <td class="text-center" data-order="<?= (int) $s['id'] ?>"><?= (int) $s['id'] ?></td>
                        <td data-search="<?= e($s['prefix']) ?>"><span class="fw-semibold text-primary-light"><?= e($s['prefix']) ?></span></td>
                        <td class="text-nowrap" data-copy-value="<?= e($s['first_code'] . ' – ' . $s['last_code']) ?>">
                            <a href="<?= e(url('/labels/' . $s['id'])) ?>" class="text-primary-600 hover-text-primary">
                                <?= e(LabelCode::format($s['first_code'])) ?> – <?= e(LabelCode::format($s['last_code'])) ?>
                            </a>
                        </td>
                        <td class="text-center"><?= (int) $s['free_count'] ?></td>
                        <td class="text-center"><?= (int) $s['assigned_count'] ?></td>
                        <td class="text-center"><?= (int) $s['spoiled_count'] ?></td>
                        <td data-order="<?= e($s['printed_at']) ?>">
                            <span class="text-nowrap"><?= e(format_datetime($s['printed_at'])) ?></span>
                            <?php if ($s['printed_by_name'] !== null): ?><span class="d-block text-secondary-light text-sm"><?= e($s['printed_by_name']) ?></span><?php endif; ?>
                        </td>
                        <td class="text-center" data-no-copy>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="<?= e(url('/labels/' . $s['id'])) ?>" title="<?= e(t('sheets.view')) ?>" aria-label="<?= e(t('sheets.view')) ?>"
                                   class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="ph ph-eye" aria-hidden="true"></i>
                                </a>
                                <a href="<?= e(url('/labels/pdf') . '?ids=' . (int) $s['id']) ?>" target="_blank" rel="noopener"
                                   title="<?= e(t('sheets.reprint')) ?>" aria-label="<?= e(t('sheets.reprint')) ?>"
                                   class="w-32-px h-32-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="ph ph-printer" aria-hidden="true"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
