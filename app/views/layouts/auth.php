<?php
use App\Core\View;

/**
 * Layout of the login and setup pages (template sign-in page).
 * @var string      $content
 * @var string|null $title
 */
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
<?= View::partial('partials/head', ['title' => $title ?? '']) ?>
</head>
<body class="position-relative z-1">

<section class="auth bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="<?= e(asset('assets/images/auth/auth-img.png')) ?>" alt="">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <div class="d-flex align-items-center gap-12 mb-40">
                <img src="<?= e(asset('assets/images/logo-icon.png')) ?>" alt="" class="w-44-px h-44-px">
                <span class="h4 mb-0 fw-bold"><?= e(config('app.name')) ?></span>
            </div>
            <?= View::partial('partials/flash') ?>
            <?= $content ?>
        </div>
    </div>
</section>

<?= View::partial('partials/scripts') ?>
</body>
</html>
