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
 *   "column" is a CSS selector of the table header cell. Checkboxes are shown
 *   as toggle buttons; consecutive ones of the same column form one group
 *   (a row is shown if its value is switched on).
 *   The filtered cell must carry data-search="<value>".
 */

$uid     = bin2hex(random_bytes(3)); // unique ids for labels
$filters = $filters ?? [];

// Consecutive checkbox filters of the same column form one toggle-button group
// (the template's "Checkbox & Radio Buttons", button.php).
$groups = [];
foreach ($filters as $f) {
    $last = count($groups) - 1;
    if ($f['type'] === 'check' && $last >= 0 && $groups[$last]['type'] === 'check'
        && $groups[$last]['items'][0]['column'] === $f['column']) {
        $groups[$last]['items'][] = $f;
    } else {
        $groups[] = ['type' => $f['type'], 'items' => [$f]];
    }
}
?>
<div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between list-toolbar" data-list-toolbar>
    <div class="d-flex align-items-center flex-wrap gap-3">
        <form class="navbar-search list-search" role="search" onsubmit="return false;">
            <input type="search" class="bg-base h-40-px" name="search" data-list-search
                   placeholder="<?= e(t('list.search')) ?>" aria-label="<?= e(t('list.search')) ?>" autocomplete="off">
            <i class="ph ph-magnifying-glass icon" aria-hidden="true"></i>
        </form>
        <?php foreach ($groups as $g => $group): ?>
            <?php if ($group['type'] === 'check'): ?>
            <div class="btn-group" role="group" aria-label="<?= e(t('list.filters')) ?>">
                <?php foreach ($group['items'] as $i => $f): $id = 'lf-' . $uid . '-' . $g . '-' . $i; ?>
                <input type="checkbox" class="btn-check" id="<?= e($id) ?>" autocomplete="off"
                       data-filter-column="<?= e($f['column']) ?>" value="<?= e($f['value']) ?>"<?= !empty($f['checked']) ? ' checked' : '' ?>>
                <label class="btn btn-outline-primary-600 h-40-px px-16 text-sm fw-medium d-inline-flex align-items-center radius-8" for="<?= e($id) ?>"><?= e($f['label']) ?></label>
                <?php endforeach; ?>
            </div>
            <?php else: $f = $group['items'][0]; $id = 'lf-' . $uid . '-' . $g; ?>
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
        <a href="<?= e($addUrl) ?>" class="btn btn-primary-600 w-40-px h-40-px p-0 radius-8 d-flex align-items-center justify-content-center"
           data-bs-toggle="tooltip" data-bs-placement="top" title="<?= e($addLabel) ?>" aria-label="<?= e($addLabel) ?>">
            <i class="ph-bold ph-plus text-xl" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
