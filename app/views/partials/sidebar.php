<?php
/**
 * Template "twin sidebar": icon rail (groups) + menu panel (items).
 * Active group/item are set on the server from $activePath.
 *
 * @var string|null $activePath
 */
$menu       = require APP_ROOT . '/config/menu.php';
$activePath = $activePath ?? '';

// Group that contains the current page (first group by default).
$activeGroup = $menu[0]['key'];
foreach ($menu as $group) {
    foreach ($group['items'] as $item) {
        if ($item['path'] === $activePath) {
            $activeGroup = $group['key'];
        }
    }
}

$renderRailButton = static function (array $group) use ($activeGroup): string {
    $active = $group['key'] === $activeGroup ? ' active' : '';
    return '<button type="button" class="rail-icon' . $active . '" data-menu="' . e($group['key']) . '" aria-label="' . e(t($group['label'])) . '">'
        . '<i class="ph ' . e($group['icon']) . '"></i>'
        . '<span class="rail-tip">' . e(t($group['label'])) . '</span></button>';
};
?>
<!-- Mobile open button -->
<button type="button" class="twin-mobile-toggle" aria-label="<?= e(t('layout.open_menu')) ?>"><i class="ph ph-list"></i></button>
<div class="twin-backdrop"></div>

<aside class="twin-sidebar" aria-label="<?= e(t('layout.open_menu')) ?>">

    <!-- Icon rail -->
    <div class="twin-rail">
        <a href="<?= e(url('/')) ?>" class="twin-rail__brand" aria-label="<?= e(config('app.name')) ?>">
            <img src="<?= e(asset('assets/images/logo-icon.png')) ?>" alt="<?= e(config('app.name')) ?>">
        </a>

        <div class="twin-rail__nav">
            <?php foreach ($menu as $group): if (($group['position'] ?? '') === 'bottom') continue; ?>
                <?= $renderRailButton($group) ?>
            <?php endforeach; ?>
        </div>

        <div class="twin-rail__bottom">
            <?php foreach ($menu as $group): if (($group['position'] ?? '') !== 'bottom') continue; ?>
                <?= $renderRailButton($group) ?>
            <?php endforeach; ?>
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
