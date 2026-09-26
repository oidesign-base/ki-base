<?php
/**
 * Card header of a list page (layout of the template's users-list.php):
 * search and quick filters on the left, icon buttons on the right.
 * The search box and filters drive the DataTable in the same card
 * (see public/assets/js/datatables-init.js).
 *
 * @var string      $addUrl    "add" button target (optional)
 * @var string      $addLabel  tooltip / accessible name of the "add" button
 * @var array       $filters   quick filters, each one:
 *     ['type' => 'check',  'column' => '.col-status', 'value' => 'active', 'label' => '…', 'checked' => true]
 *     ['type' => 'select', 'column' => '.col-category', 'label' => '…', 'options' => [value => label]]
 *   "column" is a CSS selector of the table header cell; checkboxes with the
 *   same column form one group (a row is shown if its value is checked).
 *   The filtered cell must carry data-search="<value>".
 */

$uid     = bin2hex(random_bytes(3)); // unique ids for labels
$filters = $filters ?? [];
?>
<div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between list-toolbar" data-list-toolbar>
    <div class="d-flex align-items-center flex-wrap gap-3">
        <form class="navbar-search list-search" role="search" onsubmit="return false;">
            <input type="search" class="bg-base h-40-px" name="search" data-list-search
                   placeholder="<?= e(t('list.search')) ?>" aria-label="<?= e(t('list.search')) ?>" autocomplete="off">
            <i class="ph ph-magnifying-glass icon" aria-hidden="true"></i>
        </form>
        <?php foreach ($filters as $i => $f): $id = 'lf-' . $uid . '-' . $i; ?>
            <?php if ($f['type'] === 'check'): ?>
            <div class="form-check style-check d-flex align-items-center gap-2 mb-0 h-40-px">
                <input class="form-check-input radius-4 border border-neutral-400 m-0" type="checkbox" id="<?= e($id) ?>"
                       data-filter-column="<?= e($f['column']) ?>" value="<?= e($f['value']) ?>"<?= !empty($f['checked']) ? ' checked' : '' ?>>
                <label class="form-check-label line-height-1 fw-medium text-secondary-light text-sm" for="<?= e($id) ?>"><?= e($f['label']) ?></label>
            </div>
            <?php elseif ($f['type'] === 'select'): ?>
            <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" id="<?= e($id) ?>"
                    data-filter-column="<?= e($f['column']) ?>" aria-label="<?= e($f['label']) ?>">
                <option value=""><?= e($f['label']) ?></option>
                <?php foreach ($f['options'] as $value => $label): ?>
                <option value="<?= e((string) $value) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($addUrl)): ?>
        <a href="<?= e($addUrl) ?>" class="btn btn-primary w-40-px h-40-px p-0 radius-8 d-flex align-items-center justify-content-center"
           data-bs-toggle="tooltip" data-bs-placement="top" title="<?= e($addLabel) ?>" aria-label="<?= e($addLabel) ?>">
            <i class="ph-bold ph-plus text-xl" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
