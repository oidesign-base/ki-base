<?php
use App\Core\LabelCode;
use App\Core\View;

/**
 * @var array  $prefixes    active prefixes (default first) with last_number
 * @var int[]  $quantities
 * @var string $action
 * @var string $backUrl
 */
$selectedPrefix = old('code_prefix_id', (string) ($prefixes[0]['id'] ?? ''));
$selectedQty    = old('quantity', (string) $quantities[0]);

$nextCode = static function (array $p): string {
    $next = $p['last_number'] === null ? 1 : (int) $p['last_number'] + 1;
    return $next > LabelCode::MAX_NUMBER ? '—' : LabelCode::format(LabelCode::make($p['prefix'], $next));
};
?>
<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-xl-8 col-lg-10">
                <div class="card border">
                    <div class="card-body">
                        <?php if ($prefixes === []): ?>
                        <p class="mb-16"><?= e(t('sheets.no_prefixes')) ?></p>
                        <a href="<?= e(url('/labels/prefixes/create')) ?>" class="btn btn-primary-600 text-sm px-20 py-11 radius-8"><?= e(t('prefixes.add')) ?></a>
                        <?php else: ?>
                        <form method="post" action="<?= e($action) ?>" novalidate>
                            <?= csrf_field() ?>

                            <div class="mb-20">
                                <label for="code_prefix_id" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('prefixes.prefix')) ?> <span class="text-danger-600">*</span></label>
                                <select class="form-select radius-8<?= invalid_class('code_prefix_id') ?>" id="code_prefix_id" name="code_prefix_id"
                                        data-hint-target="#next-code">
                                    <?php foreach ($prefixes as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" data-hint="<?= e(t('sheets.next_code', ['code' => $nextCode($p)])) ?>"
                                        <?= (string) $p['id'] === $selectedPrefix ? 'selected' : '' ?>>
                                        <?= e($p['prefix'] . ' — ' . $p['description']) ?><?= (int) $p['is_default'] === 1 ? ' (' . e(mb_strtolower(t('prefixes.default'))) . ')' : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?= field_feedback('code_prefix_id') ?>
                                <div class="form-text text-secondary-light text-sm mt-8" id="next-code"></div>
                            </div>

                            <div class="mb-20">
                                <span class="form-label d-block fw-semibold text-primary-light text-sm mb-8"><?= e(t('sheets.quantity')) ?> <span class="text-danger-600">*</span></span>
                                <div class="btn-group flex-wrap" role="group" aria-label="<?= e(t('sheets.quantity')) ?>">
                                    <?php foreach ($quantities as $q): ?>
                                    <input type="radio" class="btn-check" name="quantity" id="qty-<?= $q ?>" value="<?= $q ?>" autocomplete="off"
                                           <?= (string) $q === $selectedQty ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary-600 h-40-px px-16 text-sm fw-medium d-inline-flex align-items-center radius-8" for="qty-<?= $q ?>"><?= $q ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <?= field_feedback('quantity') ?>
                                <div class="form-text text-secondary-light text-sm mt-8"><?= e(t('sheets.quantity_hint')) ?></div>
                            </div>

                            <?= View::partial('components/form-buttons', ['backUrl' => $backUrl, 'submitLabel' => t('sheets.generate')]) ?>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
