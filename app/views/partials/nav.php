<?php
use App\Core\Auth;

$user = Auth::user();
?>
<div class="navbar-header">
  <div class="row align-items-center justify-content-between">
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <button type="button" class="twin-collapse line-height-1 sidebar-toggle" aria-label="<?= e(t('layout.collapse_menu')) ?>">
          <i class="ph ph-arrow-u-up-left icon non-active"></i>
          <i class="ph ph-arrow-u-up-right icon active"></i>
        </button>
      </div>
    </div>
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" data-theme-toggle title="<?= e(t('layout.theme')) ?>"
          class="w-44-px h-44-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center bg-base"></button>

        <?php if ($user !== null): ?>
        <div class="dropdown">
          <button class="d-flex justify-content-center align-items-center rounded-circle bg-transparent" type="button"
            data-bs-toggle="dropdown">
            <span class="d-flex align-items-center gap-12">
              <span class="">
                <img src="<?= e(asset('assets/images/user.png')) ?>" alt="" class="w-44-px h-44-px object-fit-cover rounded-circle">
              </span>
              <span class="d-sm-block d-none text-start">
                <span class="text-lg text-primary-light fw-semibold mb-2 d-block"><?= e($user['display_name']) ?></span>
                <span class="text-secondary-light fw-medium text-sm"><?= e($user['username']) ?></span>
              </span>
              <span class="text-neutral-500">
                <i class="ph-bold ph-caret-down"></i>
              </span>
            </span>
          </button>
          <div class="dropdown-menu to-top dropdown-menu-sm">
            <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
              <div>
                <h2 class="h6 text-lg text-primary-light fw-semibold mb-2"><?= e($user['display_name']) ?></h2>
                <span class="text-secondary-light fw-medium text-sm"><?= e($user['username']) ?></span>
              </div>
            </div>
            <ul class="to-top-list">
              <li>
                <form method="post" action="<?= e(url('/logout')) ?>" class="m-0">
                  <?= csrf_field() ?>
                  <button type="submit"
                    class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3">
                    <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> <?= e(t('layout.logout')) ?>
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
