<?php
use App\Core\Validate;
use App\Core\View;

/**
 * @var array|null $supplier
 * @var array      $countries  ISO code => name
 * @var string     $action
 * @var string     $backUrl
 */
$val = static fn (string $field, string $fallback = ''): string => old($field, $fallback);

$name       = $val('name', (string) ($supplier['name'] ?? ''));
$taxId      = $val('tax_id', Validate::formatNip($supplier['tax_id'] ?? null));
$phone      = $val('phone', (string) ($supplier['phone'] ?? ''));
$email      = $val('email', (string) ($supplier['email'] ?? ''));
$street     = $val('street', (string) ($supplier['street'] ?? ''));
$postalCode = $val('postal_code', (string) ($supplier['postal_code'] ?? ''));
$city       = $val('city', (string) ($supplier['city'] ?? ''));
$country    = $val('country_code', (string) ($supplier['country_code'] ?? 'PL'));

$label = static fn (string $for, string $text, bool $required = false): string =>
    '<label for="' . $for . '" class="form-label fw-semibold text-primary-light text-sm mb-8">' . e($text)
    . ($required ? ' <span class="text-danger-600">*</span>' : '') . '</label>';
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
                                <?= $label('name', t('suppliers.name'), true) ?>
                                <input type="text" class="form-control radius-8<?= invalid_class('name') ?>" id="name" name="name"
                                       value="<?= e($name) ?>" maxlength="150" required autofocus>
                                <?= field_feedback('name') ?>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-20">
                                    <?= $label('tax_id', t('suppliers.tax_id')) ?>
                                    <input type="text" class="form-control radius-8<?= invalid_class('tax_id') ?>" id="tax_id" name="tax_id"
                                           value="<?= e($taxId) ?>" maxlength="20" inputmode="numeric" placeholder="123-456-78-90">
                                    <?= field_feedback('tax_id') ?>
                                </div>
                                <div class="col-sm-6 mb-20">
                                    <?= $label('phone', t('suppliers.phone')) ?>
                                    <input type="tel" class="form-control radius-8<?= invalid_class('phone') ?>" id="phone" name="phone"
                                           value="<?= e($phone) ?>" maxlength="30" placeholder="+48 600 000 000">
                                    <?= field_feedback('phone') ?>
                                </div>
                            </div>

                            <div class="mb-20">
                                <?= $label('email', t('suppliers.email')) ?>
                                <input type="email" class="form-control radius-8<?= invalid_class('email') ?>" id="email" name="email"
                                       value="<?= e($email) ?>" maxlength="150" autocapitalize="none" spellcheck="false">
                                <?= field_feedback('email') ?>
                            </div>

                            <h2 class="h6 text-md text-primary-light fw-semibold mb-16 mt-8"><?= e(t('suppliers.address')) ?></h2>

                            <div class="mb-20">
                                <?= $label('street', t('suppliers.street')) ?>
                                <input type="text" class="form-control radius-8<?= invalid_class('street') ?>" id="street" name="street"
                                       value="<?= e($street) ?>" maxlength="200" placeholder="<?= e(t('suppliers.street_placeholder')) ?>">
                                <?= field_feedback('street') ?>
                            </div>

                            <div class="row">
                                <div class="col-sm-4 mb-20">
                                    <?= $label('postal_code', t('suppliers.postal_code')) ?>
                                    <input type="text" class="form-control radius-8<?= invalid_class('postal_code') ?>" id="postal_code" name="postal_code"
                                           value="<?= e($postalCode) ?>" maxlength="12" placeholder="00-000">
                                    <?= field_feedback('postal_code') ?>
                                </div>
                                <div class="col-sm-8 mb-20">
                                    <?= $label('city', t('suppliers.city')) ?>
                                    <input type="text" class="form-control radius-8<?= invalid_class('city') ?>" id="city" name="city"
                                           value="<?= e($city) ?>" maxlength="100">
                                    <?= field_feedback('city') ?>
                                </div>
                            </div>

                            <div class="mb-20">
                                <?= $label('country_code', t('suppliers.country')) ?>
                                <select class="form-control radius-8 form-select<?= invalid_class('country_code') ?>" id="country_code" name="country_code">
                                    <?php foreach ($countries as $code => $countryName): ?>
                                    <option value="<?= e($code) ?>"<?= $code === $country ? ' selected' : '' ?>><?= e($countryName) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?= field_feedback('country_code') ?>
                            </div>

                            <?= View::partial('components/form-buttons', ['backUrl' => $backUrl]) ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
