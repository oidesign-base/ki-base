<?php
use App\Core\Validate;
use App\Core\View;

/**
 * @var array|null $supplier
 * @var string     $action
 * @var string     $backUrl
 */
$name    = old('name', (string) ($supplier['name'] ?? ''));
$taxId   = old('tax_id', Validate::formatNip($supplier['tax_id'] ?? null));
$contact = old('contact', (string) ($supplier['contact'] ?? ''));
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
                                <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('suppliers.name')) ?> <span class="text-danger-600">*</span></label>
                                <input type="text" class="form-control radius-8<?= invalid_class('name') ?>" id="name" name="name"
                                       value="<?= e($name) ?>" maxlength="150" required autofocus>
                                <?= field_feedback('name') ?>
                            </div>
                            <div class="mb-20">
                                <label for="tax_id" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('suppliers.tax_id')) ?></label>
                                <input type="text" class="form-control radius-8<?= invalid_class('tax_id') ?>" id="tax_id" name="tax_id"
                                       value="<?= e($taxId) ?>" maxlength="20" inputmode="numeric" placeholder="123-456-78-90">
                                <?= field_feedback('tax_id') ?>
                            </div>
                            <div class="mb-20">
                                <label for="contact" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('suppliers.contact')) ?></label>
                                <input type="text" class="form-control radius-8<?= invalid_class('contact') ?>" id="contact" name="contact"
                                       value="<?= e($contact) ?>" maxlength="255" placeholder="<?= e(t('suppliers.contact_placeholder')) ?>">
                                <?= field_feedback('contact') ?>
                            </div>
                            <?= View::partial('components/form-buttons', ['backUrl' => $backUrl]) ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
