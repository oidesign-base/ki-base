<?php
use App\Core\Session;
use App\Core\View;

/**
 * @var array|null $prefix
 * @var bool       $locked   letters cannot change: codes with this prefix exist
 * @var string     $action
 * @var string     $backUrl
 */
$letters     = $locked ? (string) $prefix['prefix'] : old('prefix', (string) ($prefix['prefix'] ?? ''));
$description = old('description', (string) ($prefix['description'] ?? ''));

// Checkbox: after a failed submit take the submitted state, otherwise the record.
$oldInput    = Session::get('_old_input', []);
$isDefault   = is_array($oldInput) && $oldInput !== []
    ? ($oldInput['is_default'] ?? '') === '1'
    : (int) ($prefix['is_default'] ?? 0) === 1;
$defaultLock = $prefix !== null && (int) $prefix['is_default'] === 1;
?>
<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-xl-8 col-lg-10">
                <div class="card border">
                    <div class="card-body">
                        <form method="post" action="<?= e($action) ?>" novalidate>
                            <?= csrf_field() ?>

                            <div class="mb-20">
                                <label for="prefix" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('prefixes.prefix')) ?> <span class="text-danger-600">*</span></label>
                                <input type="text" class="form-control radius-8 prefix-input<?= invalid_class('prefix') ?>" id="prefix" name="prefix"
                                       value="<?= e($letters) ?>" maxlength="2" required autocomplete="off" spellcheck="false"
                                       autocapitalize="characters" placeholder="KI"<?= $locked ? ' readonly' : ' autofocus' ?>>
                                <?= field_feedback('prefix') ?>
                                <div class="form-text text-secondary-light text-sm mt-8"><?= e(t($locked ? 'prefixes.locked_hint' : 'prefixes.prefix_hint')) ?></div>
                            </div>

                            <div class="mb-20">
                                <label for="description" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('prefixes.description')) ?> <span class="text-danger-600">*</span></label>
                                <input type="text" class="form-control radius-8<?= invalid_class('description') ?>" id="description" name="description"
                                       value="<?= e($description) ?>" maxlength="200" required<?= $locked ? ' autofocus' : '' ?>
                                       placeholder="<?= e(t('prefixes.description_placeholder')) ?>">
                                <?= field_feedback('description') ?>
                            </div>

                            <div class="mb-20">
                                <div class="form-check style-check d-flex align-items-center gap-2">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" id="is_default" name="is_default" value="1"
                                           <?= $isDefault || $defaultLock ? 'checked' : '' ?><?= $defaultLock ? ' disabled' : '' ?>>
                                    <label class="form-check-label fw-medium text-secondary-light text-sm" for="is_default"><?= e(t('prefixes.is_default')) ?></label>
                                </div>
                                <?= field_feedback('is_default') ?>
                                <div class="form-text text-secondary-light text-sm mt-8"><?= e(t($defaultLock ? 'prefixes.default_locked_hint' : 'prefixes.default_hint')) ?></div>
                            </div>

                            <?= View::partial('components/form-buttons', ['backUrl' => $backUrl]) ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
