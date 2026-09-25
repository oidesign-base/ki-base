<div>
    <h1 class="h4 mb-12"><?= e(t('setup.heading')) ?></h1>
    <p class="mb-32 text-secondary-light text-lg"><?= e(t('setup.subtitle')) ?></p>
</div>
<form method="post" action="<?= e(url('/setup')) ?>" novalidate>
    <?= csrf_field() ?>

    <?php foreach ([1, 2] as $n): ?>
    <h2 class="h6 fw-semibold mb-16 <?= $n === 2 ? 'mt-32' : '' ?>">
        <?= e(t($n === 1 ? 'setup.user' : 'setup.user_optional', ['n' => $n])) ?>
    </h2>

    <div class="mb-16">
        <label class="form-label" for="username_<?= $n ?>"><?= e(t('auth.username')) ?></label>
        <input type="text" class="form-control" id="username_<?= $n ?>" name="username_<?= $n ?>"
               value="<?= e(old("username_$n")) ?>" autocomplete="off" autocapitalize="none" spellcheck="false">
        <div class="form-text"><?= e(t('setup.username_hint')) ?></div>
    </div>
    <div class="mb-16">
        <label class="form-label" for="display_name_<?= $n ?>"><?= e(t('setup.display_name')) ?></label>
        <input type="text" class="form-control" id="display_name_<?= $n ?>" name="display_name_<?= $n ?>"
               value="<?= e(old("display_name_$n")) ?>" autocomplete="off">
    </div>
    <div class="row">
        <div class="col-sm-6 mb-16">
            <label class="form-label" for="password_<?= $n ?>"><?= e(t('auth.password')) ?></label>
            <input type="password" class="form-control" id="password_<?= $n ?>" name="password_<?= $n ?>" autocomplete="new-password">
        </div>
        <div class="col-sm-6 mb-16">
            <label class="form-label" for="password_confirm_<?= $n ?>"><?= e(t('setup.password_confirm')) ?></label>
            <input type="password" class="form-control" id="password_confirm_<?= $n ?>" name="password_confirm_<?= $n ?>" autocomplete="new-password">
        </div>
    </div>
    <div class="form-text mt-0"><?= e(t('setup.password_hint', ['min' => 10])) ?></div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32"><?= e(t('setup.submit')) ?></button>
</form>
