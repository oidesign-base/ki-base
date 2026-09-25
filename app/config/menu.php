<?php
/**
 * Sidebar menu (template "twin sidebar": icon rail + menu panel).
 *
 * Each group = one rail icon + one list in the panel.
 * Labels are translation keys (app/lang/pl.php); icons are Phosphor class names.
 * 'position' => 'bottom' puts the rail icon at the bottom of the rail.
 * The dashboard has no menu item: it opens from the house icon at the top of the rail.
 */

return [
    [
        'key'   => 'stock',
        'label' => 'menu.group.stock',
        'icon'  => 'ph-package',
        'items' => [
            ['path' => '/batches',     'label' => 'menu.batches',     'icon' => 'ph-stack'],
            ['path' => '/unpacking',   'label' => 'menu.unpacking',   'icon' => 'ph-box-arrow-down'],
            ['path' => '/preparation', 'label' => 'menu.preparation', 'icon' => 'ph-camera'],
            ['path' => '/on-sale',     'label' => 'menu.on_sale',     'icon' => 'ph-storefront'],
            ['path' => '/archive',     'label' => 'menu.archive',     'icon' => 'ph-archive'],
        ],
    ],
    [
        'key'   => 'sales',
        'label' => 'menu.group.sales',
        'icon'  => 'ph-shopping-cart-simple',
        'items' => [
            ['path' => '/sales',     'label' => 'menu.sales',     'icon' => 'ph-receipt'],
            ['path' => '/shipments', 'label' => 'menu.shipments', 'icon' => 'ph-truck'],
        ],
    ],
    [
        'key'   => 'labels',
        'label' => 'menu.group.labels',
        'icon'  => 'ph-qr-code',
        'items' => [
            ['path' => '/labels', 'label' => 'menu.labels', 'icon' => 'ph-printer'],
        ],
    ],
    [
        'key'   => 'reports',
        'label' => 'menu.group.reports',
        'icon'  => 'ph-chart-line-up',
        'items' => [
            ['path' => '/reports',  'label' => 'menu.reports',  'icon' => 'ph-chart-bar'],
            ['path' => '/activity', 'label' => 'menu.activity', 'icon' => 'ph-clock-counter-clockwise'],
        ],
    ],
    [
        'key'      => 'settings',
        'label'    => 'menu.group.settings',
        'icon'     => 'ph-gear-six',
        'position' => 'bottom',
        'items'    => [
            ['path' => '/settings/categories', 'label' => 'menu.categories', 'icon' => 'ph-tag'],
            ['path' => '/settings/suppliers',  'label' => 'menu.suppliers',  'icon' => 'ph-handshake'],
            ['path' => '/settings/platforms',  'label' => 'menu.platforms',  'icon' => 'ph-globe'],
            ['path' => '/settings/carriers',   'label' => 'menu.carriers',   'icon' => 'ph-truck'],
            ['path' => '/settings/tax',        'label' => 'menu.tax',        'icon' => 'ph-percent'],
            ['path' => '/settings/users',      'label' => 'menu.users',      'icon' => 'ph-users-three'],
        ],
    ],
];
