<?php
use App\Core\LabelCode;
use App\Core\View;

/**
 * @var array      $generations  one row per generation (1 ... 5 sheets of one prefix)
 * @var array      $prefixes     prefixes that have codes (for the filter)
 * @var array|null $download     just generated: ['id' => generation, 'ids' => sheet ids, 'range' => '…']
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
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(url('/labels/' . (int) $download['id'])) ?>"
           class="btn btn-outline-primary-600 text-sm px-20 h-40-px radius-8 d-inline-flex align-items-center gap-2">
            <i class="ph ph-squares-four text-xl" aria-hidden="true"></i><?= e(t('sheets.view')) ?>
        </a>
        <a href="<?= e(url('/labels/pdf') . '?ids=' . implode(',', array_map('intval', $download['ids']))) ?>" target="_blank" rel="noopener"
           class="btn btn-primary-600 text-sm px-20 h-40-px radius-8 d-inline-flex align-items-center gap-2">
            <i class="ph ph-file-pdf text-xl" aria-hidden="true"></i><?= e(t('sheets.download_pdf')) ?>
        </a>
    </div>
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
                        <th scope="col" class="col-prefix"><?= e(t('prefixes.prefix')) ?></th>
                        <th scope="col"><?= e(t('sheets.codes')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.sheets_count')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.free')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.assigned')) ?></th>
                        <th scope="col" class="col-count text-center"><?= e(t('sheets.spoiled_count')) ?></th>
                        <th scope="col"><?= e(t('sheets.printed')) ?></th>
                        <th scope="col" class="col-actions text-center" data-orderable="false"><?= e(t('field.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($generations as $g): $sheetIds = (string) $g['sheet_ids']; ?>
                    <tr>
                        <td data-search="<?= e($g['prefix']) ?>"><span class="fw-semibold text-primary-light"><?= e($g['prefix']) ?></span></td>
                        <td data-order="<?= e($g['first_code']) ?>" data-copy-value="<?= e($g['first_code'] . ' – ' . $g['last_code']) ?>">
                            <a href="<?= e(url('/labels/' . $g['id'])) ?>" class="text-primary-600 hover-text-primary">
                                <span class="text-nowrap"><?= e(LabelCode::format($g['first_code'])) ?> –</span>
                                <span class="text-nowrap"><?= e(LabelCode::format($g['last_code'])) ?></span>
                            </a>
                        </td>
                        <td class="text-center"><?= $sheetIds === '' ? 0 : count(explode(',', $sheetIds)) ?></td>
                        <td class="text-center"><?= (int) $g['free_count'] ?></td>
                        <td class="text-center"><?= (int) $g['assigned_count'] ?></td>
                        <td class="text-center"><?= (int) $g['spoiled_count'] ?></td>
                        <td data-order="<?= e($g['created_at']) ?>">
                            <span class="text-nowrap"><?= e(format_datetime($g['created_at'])) ?></span>
                            <?php if ($g['created_by_name'] !== null): ?><span class="d-block text-secondary-light text-sm"><?= e($g['created_by_name']) ?></span><?php endif; ?>
                        </td>
                        <td class="text-center" data-no-copy>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="<?= e(url('/labels/' . $g['id'])) ?>" title="<?= e(t('sheets.view')) ?>" aria-label="<?= e(t('sheets.view')) ?>"
                                   class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="ph ph-eye" aria-hidden="true"></i>
                                </a>
                                <a href="<?= e(url('/labels/pdf') . '?ids=' . $sheetIds) ?>" target="_blank" rel="noopener"
                                   title="<?= e(t('sheets.print_all')) ?>" aria-label="<?= e(t('sheets.print_all')) ?>"
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
