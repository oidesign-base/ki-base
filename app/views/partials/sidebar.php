<?php
/**
 * Template "twin sidebar": icon rail (groups) + menu panel (items).
 *
 * Rail, top to bottom: house (dashboard), menu groups, then at the bottom:
 * collapse/expand, settings group, light/dark theme, user avatar (logout menu).
 * Active group/item are set on the server from $activePath.
 *
 * @var string|null $activePath
 */
use App\Core\Auth;

$menu       = require APP_ROOT . '/config/menu.php';
$activePath = $activePath ?? '';
$user       = Auth::user();

// Group that contains the current page (first group by default, e.g. on the dashboard).
$activeGroup = $menu[0]['key'];
foreach ($menu as $group) {
    foreach ($group['items'] as $item) {
        if ($item['path'] === $activePath) {
            $activeGroup = $group['key'];
        }
    }
}
$isDashboard = $activePath === '/';

$renderRailButton = static function (array $group) use ($activeGroup, $isDashboard): string {
    $active = !$isDashboard && $group['key'] === $activeGroup ? ' active' : '';
    return '<button type="button" class="rail-icon' . $active . '" data-menu="' . e($group['key']) . '" aria-label="' . e(t($group['label'])) . '">'
        . '<i class="ph ' . e($group['icon']) . '"></i>'
        . '<span class="rail-tip">' . e(t($group['label'])) . '</span></button>';
};
?>
<!-- Mobile open button -->
<button type="button" class="twin-mobile-toggle" aria-label="<?= e(t('layout.open_menu')) ?>"><i class="ph ph-list"></i></button>
<div class="twin-backdrop"></div>

<aside class="twin-sidebar" aria-label="<?= e(t('layout.menu')) ?>">

    <!-- Icon rail -->
    <div class="twin-rail">
        <a href="<?= e(url('/')) ?>" class="rail-icon rail-home<?= $isDashboard ? ' active' : '' ?>" aria-label="<?= e(t('menu.dashboard')) ?>">
            <i class="ph ph-house-simple"></i><span class="rail-tip"><?= e(t('menu.dashboard')) ?></span>
        </a>

        <div class="twin-rail__nav">
            <?php foreach ($menu as $group): if (($group['position'] ?? '') === 'bottom') continue; ?>
                <?= $renderRailButton($group) ?>
            <?php endforeach; ?>
        </div>

        <div class="twin-rail__bottom">
            <button type="button" class="rail-icon twin-collapse" aria-label="<?= e(t('layout.collapse_menu')) ?>">
                <i class="ph ph-caret-left collapse-icon-open"></i>
                <i class="ph ph-caret-right collapse-icon-closed"></i>
                <span class="rail-tip"><?= e(t('layout.expand_menu')) ?></span>
            </button>

            <?php foreach ($menu as $group): if (($group['position'] ?? '') !== 'bottom') continue; ?>
                <?= $renderRailButton($group) ?>
            <?php endforeach; ?>

            <button type="button" data-theme-toggle class="rail-theme-toggle" aria-label="light" title="<?= e(t('layout.theme')) ?>"></button>

            <?php if ($user !== null): ?>
            <div class="dropend">
                <button type="button" class="rail-avatar w-44-px h-44-px bg-info-subtle text-info-main rounded-circle d-flex justify-content-center align-items-center fw-semibold border-0"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?= e($user['display_name']) ?>" title="<?= e($user['display_name']) ?>">
                    <?= e(user_initials($user['display_name'])) ?>
                </button>
                <div class="dropdown-menu dropdown-menu-sm p-12">
                    <div class="py-12 px-16 radius-8 bg-primary-50 mb-12">
                        <span class="text-lg text-primary-light fw-semibold d-block"><?= e($user['display_name']) ?></span>
                        <span class="text-secondary-light fw-medium text-sm"><?= e($user['username']) ?></span>
                    </div>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-black px-16 py-8 hover-text-danger d-flex align-items-center gap-3 radius-8">
                            <i class="ph ph-sign-out text-xl"></i> <?= e(t('layout.logout')) ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menu panel -->
    <div class="twin-panel">
        <div class="twin-panel__head">
            <a href="<?= e(url('/')) ?>" class="twin-panel__logo"><?= e(config('app.name')) ?></a>
        </div>

        <div class="twin-panel__body">
            <?php foreach ($menu as $group): ?>
                <ul class="twin-menu<?= $group['key'] === $activeGroup ? ' active' : '' ?>" data-menu="<?= e($group['key']) ?>">
                    <?php foreach ($group['items'] as $item): ?>
                        <li class="<?= $item['path'] === $activePath ? 'active' : '' ?>">
                            <a href="<?= e(url($item['path'])) ?>"><i class="ph <?= e($item['icon']) ?>"></i><span><?= e(t($item['label'])) ?></span></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>

        <div class="twin-panel__foot">
            <span class="twin-version">v<?= e(config('app.version')) ?></span>
        </div>
    </div>
</aside>
