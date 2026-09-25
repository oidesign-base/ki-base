<?php
use App\Core\Auth;

/** @var array $stats */
$cards = [
    ['label' => 'dashboard.open_batches',    'value' => $stats['open_batches'] ?? 0,    'icon' => 'ph-stack',      'url' => '/batches'],
    ['label' => 'dashboard.in_work',         'value' => $stats['in_work'] ?? 0,         'icon' => 'ph-package',    'url' => '/unpacking'],
    ['label' => 'dashboard.listed',          'value' => $stats['listed'] ?? 0,          'icon' => 'ph-storefront', 'url' => '/on-sale'],
    ['label' => 'dashboard.sold_this_month', 'value' => $stats['sold_this_month'] ?? 0, 'icon' => 'ph-receipt',    'url' => '/sales'],
];
?>
<div class="bg-base p-24 radius-20 mb-20">
    <h2 class="h6 fw-semibold mb-8"><?= e(t('dashboard.welcome', ['name' => Auth::user()['display_name'] ?? ''])) ?></h2>
    <p class="text-secondary-light mb-0"><?= e(t('dashboard.intro')) ?></p>
</div>

<div class="row gy-20-px">
    <?php foreach ($cards as $card): ?>
    <div class="col-xxl-3 col-sm-6">
        <a href="<?= e(url($card['url'])) ?>" class="d-block bg-base p-24 radius-20 h-100">
            <div class="d-flex align-items-center justify-content-between gap-8 pb-20 mb-20 border-bottom border-neutral-200">
                <span class="text-neutral-700 fw-medium"><?= e(t($card['label'])) ?></span>
                <span class="text-primary-600 text-2xl line-height-1"><i class="ph <?= e($card['icon']) ?>"></i></span>
            </div>
            <h3 class="text-2xl mb-0 text-primary-light"><?= (int) $card['value'] ?></h3>
        </a>
    </div>
    <?php endforeach; ?>
</div>
