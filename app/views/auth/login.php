<div>
    <h1 class="h4 mb-12"><?= e(t('auth.login.heading')) ?></h1>
    <p class="mb-32 text-secondary-light text-lg"><?= e(t('auth.login.subtitle')) ?></p>
</div>
<form method="post" action="<?= e(url('/login')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="icon-field mb-16">
        <span class="icon top-50 translate-middle-y">
            <iconify-icon icon="solar:user-outline"></iconify-icon>
        </span>
        <input type="text" name="username" value="<?= e(old('username')) ?>"
               class="form-control h-56-px bg-neutral-50 radius-12"
               placeholder="<?= e(t('auth.username')) ?>" aria-label="<?= e(t('auth.username')) ?>"
               autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
    </div>
    <div class="position-relative mb-20">
        <div class="icon-field">
            <span class="icon top-50 translate-middle-y">
                <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
            </span>
            <input type="password" name="password" id="password"
                   class="form-control h-56-px bg-neutral-50 radius-12"
                   placeholder="<?= e(t('auth.password')) ?>" aria-label="<?= e(t('auth.password')) ?>"
                   autocomplete="current-password" required>
        </div>
        <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
              data-toggle="#password" role="button" aria-label="<?= e(t('auth.show_password')) ?>"></span>
    </div>

    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32"><?= e(t('auth.login.submit')) ?></button>
</form>
