<?php
/**
 * Ukrainian UI strings. Keys are English; values are shown in the panel.
 */

return [
    // Menu
    'menu.group.home'     => 'Головна',
    'menu.group.stock'    => 'Облік',
    'menu.group.sales'    => 'Продажі',
    'menu.group.labels'   => 'Наклейки',
    'menu.group.reports'  => 'Звіти',
    'menu.group.settings' => 'Налаштування',
    'menu.dashboard'      => 'Огляд',
    'menu.batches'        => 'Партії',
    'menu.unpacking'      => 'Розпакування',
    'menu.preparation'    => 'Підготовка',
    'menu.on_sale'        => 'У продажу',
    'menu.archive'        => 'Архів',
    'menu.sales'          => 'Продажі',
    'menu.shipments'      => 'Відправки',
    'menu.labels'         => 'Аркуші кодів',
    'menu.reports'        => 'Звіти',
    'menu.activity'       => 'Журнал дій',
    'menu.categories'     => 'Категорії',
    'menu.platforms'      => 'Платформи',
    'menu.carriers'       => 'Перевізники',
    'menu.tax'            => 'Податки',
    'menu.users'          => 'Користувачі',

    // Layout
    'layout.home'            => 'Головна',
    'layout.open_menu'       => 'Відкрити меню',
    'layout.collapse_menu'   => 'Згорнути меню',
    'layout.theme'           => 'Світла / темна тема',
    'layout.logout'          => 'Вийти',
    'layout.footer'          => 'Панель обліку товарів',

    // Login
    'auth.login.title'    => 'Вхід',
    'auth.login.heading'  => 'Вхід у панель',
    'auth.login.subtitle' => 'Введіть логін і пароль',
    'auth.username'       => 'Логін',
    'auth.password'       => 'Пароль',
    'auth.show_password'  => 'Показати пароль',
    'auth.login.submit'   => 'Увійти',
    'auth.login.required' => 'Введіть логін і пароль.',
    'auth.login.invalid'  => 'Неправильний логін або пароль.',
    'auth.login.locked'   => 'Забагато невдалих спроб. Спробуйте через {minutes} хв.',
    'auth.logout.done'    => 'Ви вийшли з панелі.',

    // Setup
    'setup.title'                   => 'Початкове налаштування',
    'setup.heading'                 => 'Створення користувачів',
    'setup.subtitle'                => 'Ця сторінка працює один раз — поки в панелі немає жодного користувача.',
    'setup.user'                    => 'Користувач {n}',
    'setup.user_optional'           => 'Користувач {n} (необов\'язково)',
    'setup.display_name'            => 'Ім\'я в панелі',
    'setup.password_confirm'        => 'Пароль ще раз',
    'setup.username_hint'           => 'Латиниця, цифри, крапка, дефіс, підкреслення; 3–50 символів.',
    'setup.password_hint'           => 'Щонайменше {min} символів.',
    'setup.submit'                  => 'Створити',
    'setup.done'                    => 'Користувачів створено. Увійдіть у панель.',
    'setup.error.username'          => 'Користувач {n}: логін має містити 3–50 символів — латиниця, цифри, крапка, дефіс, підкреслення.',
    'setup.error.display_name'      => 'Користувач {n}: вкажіть ім\'я (до 100 символів).',
    'setup.error.password_short'    => 'Користувач {n}: пароль має бути щонайменше {min} символів.',
    'setup.error.password_mismatch' => 'Користувач {n}: паролі не збігаються.',
    'setup.error.same_username'     => 'Логіни двох користувачів мають відрізнятися.',

    // Dashboard
    'dashboard.welcome'          => 'Вітаємо, {name}!',
    'dashboard.intro'            => 'Каркас панелі працює. Розділи з\'являтимуться в меню в міру розробки.',
    'dashboard.open_batches'     => 'Відкриті партії',
    'dashboard.in_work'          => 'Товари в роботі',
    'dashboard.listed'           => 'Виставлено',
    'dashboard.sold_this_month'  => 'Продано цього місяця',

    // Placeholder
    'section.placeholder.heading' => 'Розділ у розробці',
    'section.placeholder.text'    => 'Цей розділ з\'явиться в одному з наступних етапів.',
    'section.back_home'           => 'На головну',

    // Errors
    'error.403.title' => 'Доступ заборонено',
    'error.403.text'  => 'У вас немає доступу до цієї сторінки.',
    'error.404.title' => 'Сторінку не знайдено',
    'error.404.text'  => 'Такої сторінки немає або її адреса змінилася.',
    'error.405.title' => 'Метод не дозволено',
    'error.405.text'  => 'Цю сторінку не можна відкрити таким способом.',
    'error.419.title' => 'Сторінка застаріла',
    'error.419.text'  => 'Форма була відкрита надто довго або сесія завершилась. Оновіть сторінку й спробуйте ще раз.',
    'error.500.title' => 'Помилка сервера',
    'error.500.text'  => 'Щось пішло не так. Подробиці записано в журнал помилок.',
    'error.503.title' => 'Панель не налаштована',
    'error.503.text'  => 'Не знайдено конфігурацію з доступом до бази даних (app/config/config.local.php).',
];
