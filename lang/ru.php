<?php

declare(strict_types=1);

/**
 * Russian UI strings. Keys are dot-namespaced by area. Used via Lang::t('key')
 * in PHP and {{ key }} in .tpl templates. English lives in en.php.
 */
return [
    // top navigation / chrome
    'nav.home'            => 'Главная',
    'nav.delivery'        => 'Оплата и доставка',
    'nav.feedback'        => 'Обратная связь',
    'auth.login_register' => 'Вход / Регистрация',
    'auth.account'        => 'Личный кабинет',
    'auth.admin'          => 'Админ',
    'auth.admin_title'    => 'Админ-панель',
    'lang.label'          => 'Язык',
    'shop.name'           => 'Homy.su — каталог бытовой техники',
    'shop.address'        => '305000, г. Курск, Элеваторный проезд, 14',

    // search
    'search.placeholder'  => 'Поиск по каталогу...',
    'search.aria_field'   => 'Поиск товаров',
    'search.aria_button'  => 'Найти',
    'search.menu_aria'    => 'Категории товаров',

    // cart dropdown
    'cart.label'          => 'Корзина:',
    'cart.empty_short'    => 'пуста',
    'cart.dropdown_empty' => 'Ваша корзина пуста',
    'cart.to_cart'        => 'В корзину',
    'cart.checkout'       => 'Оформить заказ',
    'cart.qty_suffix'     => 'шт.',

    // breadcrumb
    'breadcrumb.catalog'  => 'Каталог',
    'breadcrumb.back'     => 'Назад',

    // footer
    'footer.services'       => 'Сервисы',
    'footer.account'        => 'Аккаунт',
    'footer.about'          => 'О магазине',
    'footer.delivery'       => 'Оплата и доставка',
    'footer.feedback'       => 'Обратная связь',
    'footer.credits'        => 'Источники изображений',
    'footer.account_login'  => 'Вход / Регистрация',
    'footer.account_cabinet' => 'Личный кабинет',
    'footer.catalog'        => 'Каталог товаров',
    'footer.subscribe'      => 'Подписаться на нашу рассылку',
    'footer.go'             => 'Вперёд!',
    'footer.social'         => 'Социальные сети',
    'footer.about_short'    => 'Демо-версия магазина. Это портфолио-проект.',
    'footer.tagline'        => 'портфолио-проект.',
    'footer.oferta'         => 'Демо-версия. Заказы не оформляются по-настоящему.',

    // home
    'home.new_arrivals'   => 'Новинки каталога',
    'home.brands'         => 'Производители',
    'home.meta_title'     => 'Homy — каталог бытовой техники и электроники',
    'home.meta_desc'      => 'Демо-витрина магазина бытовой техники: смартфоны, ноутбуки, ТВ, аудио и техника для дома.',
    'home.meta_keywords'  => 'бытовая техника, электроника, смартфоны, ноутбуки, ТВ, демо',
    'hero.kicker'         => 'ДЕМО-ПОРТФОЛИО',
    'hero.title_line1'    => 'Бытовая техника,',
    'hero.title_accent'   => 'собранная для демо',
    'hero.subtitle'       => 'PHP 8 · файловое хранилище · реальные фото с Wikimedia Commons',
    'hero.cta_catalog'    => 'Открыть каталог →',
    'hero.cta_about'      => 'О проекте',

    // catalog index
    'catalog.title'       => 'Каталог',
    'catalog.intro'       => 'В каталоге {count} {word}.',
    'catalog.meta_title'  => 'Каталог бытовой техники и электроники',
    'catalog.meta_desc'   => 'Каталог категорий товаров.',
    'category.count'      => '{count} тов.',
    'search.results_count' => '{count} {word}',

    // category page
    'category.brand_filter' => 'Производитель',
    'category.reset_filter' => 'Сбросить фильтр',
    'category.sort_label'   => 'Сортировка:',
    'category.sort_default' => 'По умолчанию',
    'category.sort_price_asc'  => 'Цена ↑',
    'category.sort_price_desc' => 'Цена ↓',
    'category.sort_rating'  => 'По рейтингу',
    'category.empty'        => 'В этой категории пока нет товаров.',
    'category.meta_prefix'  => 'Категория',

    // product page
    'product.availability'  => 'Доступность:',
    'product.in_stock'      => 'на складе',
    'product.out_stock'     => 'под заказ',
    'product.add_to_cart'   => 'В корзину',
    'product.in_cart'       => 'В корзине',
    'product.specs_tab'     => 'Характеристики',
    'product.reviews_tab'   => 'Отзывы',
    'product.demo_note'     => '* Это демо-данные. Реальные характеристики могут отличаться.',
    'product.specs_none'    => 'Характеристики товара будут доступны позже.',
    'product.specs_heading' => 'Основные характеристики',
    'product.reviews_none'  => 'Отзывов пока нет.',
    'product.review_grade'  => 'Оценка: {n} / 5',
    'product.review_pros'   => 'Плюсы:',
    'product.review_cons'   => 'Минусы:',
    'product.same_category' => 'Похожие товары',

    // cart page
    'cartpage.title'       => 'Корзина',
    'cartpage.empty_title' => 'Корзина пуста',
    'cartpage.empty_text'  => 'Добавьте товары из {link}.',
    'cartpage.empty_link'  => 'каталога',
    'cartpage.col_product' => 'Товар',
    'cartpage.col_price'   => 'Цена',
    'cartpage.col_qty'     => 'Кол-во',
    'cartpage.col_sum'     => 'Сумма',
    'cartpage.total'       => 'Итого',
    'cartpage.update'      => 'Обновить',
    'cartpage.checkout'    => 'Оформить заказ',

    // checkout
    'checkout.title'        => 'Оформление заказа',
    'checkout.name'         => 'Имя',
    'checkout.phone'        => 'Телефон',
    'checkout.address'      => 'Адрес доставки',
    'checkout.comment'      => 'Комментарий',
    'checkout.submit'       => 'Оформить',
    'checkout.your_order'   => 'Ваш заказ',
    'checkout.total'        => 'Итого',
    'checkout.error_required' => 'Заполните все обязательные поля.',
    'checkout.success_title'  => 'Спасибо, заказ оформлен!',
    'checkout.success_number' => 'Номер вашего заказа: {id}',
    'checkout.success_demo'   => 'Это демо: заказ записан в storage/runtime/orders.json.',
    'checkout.to_home'        => 'На главную',
    'checkout.success_meta'   => 'Заказ оформлен',

    // search results
    'search.none'          => 'По запросу «{q}» ничего не найдено. Попробуйте другое слово или {link}.',
    'search.none_link'     => 'перейдите в каталог',
    'search.results_title' => 'Поиск: «{q}»',
    'search.meta'          => 'Поиск: {q}',
    'search.breadcrumb'    => 'Поиск',
    'search.ac_none'       => 'Ничего не найдено',
    'search.ac_catalog'    => 'Каталог',

    // login
    'login.title'       => 'Вход',
    'login.password'    => 'Пароль',
    'login.submit'      => 'Войти',
    'login.demo_users'  => 'В демо-версии есть тестовые пользователи:',
    'login.error_empty' => 'Введите e-mail и пароль.',
    'login.error_bad'   => 'Пара e-mail/пароль не подходит.',

    // account
    'account.title'     => 'Личный кабинет',
    'account.greeting'  => 'Здравствуйте, {name}!',
    'account.my_orders' => 'Мои заказы ({n})',
    'account.logout'    => 'Выйти',
    'orders.title'      => 'Мои заказы',
    'orders.col_number' => 'Номер',
    'orders.col_date'   => 'Дата',
    'orders.col_items'  => 'Позиций',
    'orders.col_sum'    => 'Сумма',
    'orders.col_status' => 'Статус',
    'orders.none'       => 'У вас ещё нет заказов.',
    'orders.back'       => '← В кабинет',

    // 404
    'error.404_title' => '404 — Страница не найдена',
    'error.404_text'  => 'Запрошенный адрес {path} не существует.',
    'error.to_home'   => 'На главную',

    // terms
    'terms.title'     => 'Оплата и доставка',
    'terms.intro'     => 'Это демо-страница условий продажи. В реальном магазине здесь были бы условия оплаты, способы и сроки доставки, гарантии и контакты.',
    'terms.pay_head'  => 'Способы оплаты',
    'terms.pay_cash'  => 'Наличными при получении',
    'terms.pay_card'  => 'Банковской картой',
    'terms.pay_wire'  => 'Безналичный расчёт',
    'terms.ship_head' => 'Доставка',
    'terms.ship_text' => 'Бесплатная доставка по городу при сумме заказа от 5 000 ₽.',

    // feedback
    'feedback.title'   => 'Обратная связь',
    'feedback.note'    => 'Демо-форма. Поля проверяются на стороне браузера, отправка ничего не делает.',
    'feedback.name'    => 'Имя',
    'feedback.message' => 'Сообщение',
    'feedback.submit'  => 'Отправить',
    'feedback.sent'    => 'Спасибо! Это демо-форма — сообщение нигде не сохраняется.',

    // credits
    'credits.title'        => 'Источники изображений',
    'credits.intro'        => 'Это некоммерческий учебный портфолио-проект. Фотографии товаров взяты из Wikimedia Commons и используются по их свободным лицензиям (CC BY-SA, CC BY, CC0, общественное достояние). Логотипы брендов являются товарными знаками соответствующих владельцев и приведены исключительно для идентификации товаров.',
    'credits.logos_head'   => 'Логотипы брендов',
    'credits.logos_note'   => 'Товарные знаки соответствующих владельцев.',
    'credits.photos_head'  => 'Фотографии товаров',
    'credits.photos_note'  => 'Источник: Wikimedia Commons.',
    'credits.col_brand'    => 'Бренд',
    'credits.col_product'  => 'Товар',
    'credits.col_author'   => 'Автор',
    'credits.col_license'  => 'Лицензия',
    'credits.col_source'   => 'Источник',

    // about
    'about.title' => 'О магазине',
    'about.p1'    => 'Это портфолио-проект — переписанный современный фронтенд магазина бытовой техники.',
    'about.p2'    => 'Оригинальный код был построен на самописном PHP-движке с прямым доступом к MySQL и MongoDB. В демо-версии все данные хранятся в JSON-файлах в каталоге storage/data/, авторизация работает на bcrypt, маршрутизация — на FastRoute, а логирование — на Monolog.',
    'about.p3'    => 'Цель проекта — продемонстрировать рефакторинг кода: удаление зависимостей от инфраструктуры, переход на PSR-4, чистую структуру действий, репозиториев и сервисов.',
    'about.demo_head'  => 'Что демонстрирует проект',
    'about.demo_1'     => 'каталог товаров с категориями, брендами и фильтрами;',
    'about.demo_2'     => 'карточку товара с характеристиками и отзывами;',
    'about.demo_3'     => 'корзину и оформление заказа (заказы пишутся в JSON);',
    'about.demo_4'     => 'авторизацию по bcrypt-паролю на демо-пользователях;',
    'about.demo_5'     => 'поиск по каталогу.',
    'about.stack_head' => 'Стек',
    'about.stack_route' => 'nikic/fast-route — маршрутизация',
    'about.stack_log'   => 'monolog/monolog — логирование',
    'about.stack_uuid'  => 'ramsey/uuid — генерация UUID',
    'about.stack_env'   => 'vlucas/phpdotenv — конфигурация',
    'about.stack_tpl'   => 'простой шаблонизатор на {…}-подстановках',

    // plural words (Russian needs 3 forms; English uses .one/.other)
    'word.items.one'   => 'позиция',
    'word.items.few'   => 'позиции',
    'word.items.many'  => 'позиций',
    'word.products'    => 'товаров',
    'word.results.one' => 'результат',
    'word.results.few' => 'результата',
    'word.results.many' => 'результатов',
];
