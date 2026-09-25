<?php
use App\Core\View;

/**
 * @var array|null $category
 * @var string     $action
 * @var string     $backUrl
 */
$name = old('name', (string) ($category['name'] ?? ''));
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
                                <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8"><?= e(t('categories.name')) ?> <span class="text-danger-600">*</span></label>
                                <input type="text" class="form-control radius-8<?= invalid_class('name') ?>" id="name" name="name"
                                       value="<?= e($name) ?>" maxlength="100" required autofocus
                                       placeholder="<?= e(t('categories.name_placeholder')) ?>">
                                <?= field_feedback('name') ?>
                            </div>
                            <?= View::partial('components/form-buttons', ['backUrl' => $backUrl]) ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
