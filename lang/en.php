<?php

declare(strict_types=1);

/**
 * English UI strings (default language). Mirrors the keys in ru.php.
 * Used via Lang::t('key') in PHP and {{ key }} in .tpl templates.
 */
return [
    // top navigation / chrome
    'nav.home'            => 'Home',
    'nav.delivery'        => 'Delivery & Payment',
    'nav.feedback'        => 'Contact',
    'auth.login_register' => 'Sign in / Register',
    'auth.account'        => 'My Account',
    'auth.admin'          => 'Admin',
    'auth.admin_title'    => 'Admin panel',
    'lang.label'          => 'Language',
    'shop.name'           => 'Homy.su — home appliances catalog',
    'shop.address'        => '305000, Kursk, Elevatorny proezd 14',

    // search
    'search.placeholder'  => 'Search the catalog...',
    'search.aria_field'   => 'Search products',
    'search.aria_button'  => 'Search',
    'search.menu_aria'    => 'Product categories',

    // cart dropdown
    'cart.label'          => 'Cart:',
    'cart.empty_short'    => 'empty',
    'cart.dropdown_empty' => 'Your cart is empty',
    'cart.to_cart'        => 'View cart',
    'cart.checkout'       => 'Checkout',
    'cart.qty_suffix'     => 'pcs',

    // breadcrumb
    'breadcrumb.catalog'  => 'Catalog',
    'breadcrumb.back'     => 'Back',

    // footer
    'footer.services'       => 'Services',
    'footer.account'        => 'Account',
    'footer.about'          => 'About',
    'footer.delivery'       => 'Delivery & Payment',
    'footer.feedback'       => 'Contact',
    'footer.credits'        => 'Image credits',
    'footer.account_login'  => 'Sign in / Register',
    'footer.account_cabinet' => 'My Account',
    'footer.catalog'        => 'Product catalog',
    'footer.subscribe'      => 'Subscribe to our newsletter',
    'footer.go'             => 'Go!',
    'footer.social'         => 'Social networks',
    'footer.about_short'    => 'Store demo. A portfolio project.',
    'footer.tagline'        => 'a portfolio project.',
    'footer.oferta'         => 'Demo version. No real orders are placed.',

    // home
    'home.new_arrivals'   => 'New arrivals',
    'home.brands'         => 'Brands',
    'home.meta_title'     => 'Homy — home appliances & electronics catalog',
    'home.meta_desc'      => 'Demo storefront for home appliances: smartphones, laptops, TVs, audio and home electronics.',
    'home.meta_keywords'  => 'home appliances, electronics, smartphones, laptops, TV, demo',
    'hero.kicker'         => 'PORTFOLIO DEMO',
    'hero.title_line1'    => 'Home appliances,',
    'hero.title_accent'   => 'curated for the demo',
    'hero.subtitle'       => 'PHP 8 · file-based storage · real photos from Wikimedia Commons',
    'hero.cta_catalog'    => 'Open the catalog →',
    'hero.cta_about'      => 'About the project',

    // catalog index
    'catalog.title'       => 'Catalog',
    'catalog.intro'       => 'The catalog has {count} {word}.',
    'catalog.meta_title'  => 'Home appliances & electronics catalog',
    'catalog.meta_desc'   => 'Product category catalog.',
    'category.count'      => '{count} pcs',
    'search.results_count' => '{count} {word}',

    // category page
    'category.brand_filter' => 'Brand',
    'category.reset_filter' => 'Reset filter',
    'category.sort_label'   => 'Sort:',
    'category.sort_default' => 'Default',
    'category.sort_price_asc'  => 'Price ↑',
    'category.sort_price_desc' => 'Price ↓',
    'category.sort_rating'  => 'By rating',
    'category.empty'        => 'No products in this category yet.',
    'category.meta_prefix'  => 'Category',

    // product page
    'product.availability'  => 'Availability:',
    'product.in_stock'      => 'in stock',
    'product.out_stock'     => 'made to order',
    'product.add_to_cart'   => 'Add to cart',
    'product.in_cart'       => 'In cart',
    'product.specs_tab'     => 'Specifications',
    'product.reviews_tab'   => 'Reviews',
    'product.demo_note'     => '* Demo data. Real specifications may differ.',
    'product.specs_none'    => 'Specifications will be available later.',
    'product.specs_heading' => 'Key specifications',
    'product.reviews_none'  => 'No reviews yet.',
    'product.review_grade'  => 'Rating: {n} / 5',
    'product.review_pros'   => 'Pros:',
    'product.review_cons'   => 'Cons:',
    'product.same_category' => 'Related products',

    // cart page
    'cartpage.title'       => 'Cart',
    'cartpage.empty_title' => 'Cart is empty',
    'cartpage.empty_text'  => 'Add products from the {link}.',
    'cartpage.empty_link'  => 'catalog',
    'cartpage.col_product' => 'Product',
    'cartpage.col_price'   => 'Price',
    'cartpage.col_qty'     => 'Qty',
    'cartpage.col_sum'     => 'Subtotal',
    'cartpage.total'       => 'Total',
    'cartpage.update'      => 'Update',
    'cartpage.checkout'    => 'Checkout',

    // checkout
    'checkout.title'        => 'Checkout',
    'checkout.name'         => 'Name',
    'checkout.phone'        => 'Phone',
    'checkout.address'      => 'Delivery address',
    'checkout.comment'      => 'Comment',
    'checkout.submit'       => 'Place order',
    'checkout.your_order'   => 'Your order',
    'checkout.total'        => 'Total',
    'checkout.error_required' => 'Please fill in all required fields.',
    'checkout.success_title'  => 'Thank you, your order is placed!',
    'checkout.success_number' => 'Your order number: {id}',
    'checkout.success_demo'   => 'This is a demo: the order was written to storage/runtime/orders.json.',
    'checkout.to_home'        => 'Back to home',
    'checkout.success_meta'   => 'Order placed',

    // search results
    'search.none'          => 'Nothing found for “{q}”. Try another word or {link}.',
    'search.none_link'     => 'browse the catalog',
    'search.results_title' => 'Search: “{q}”',
    'search.meta'          => 'Search: {q}',
    'search.breadcrumb'    => 'Search',
    'search.ac_none'       => 'Nothing found',
    'search.ac_catalog'    => 'Catalog',

    // login
    'login.title'       => 'Sign in',
    'login.password'    => 'Password',
    'login.submit'      => 'Sign in',
    'login.demo_users'  => 'The demo has test users:',
    'login.error_empty' => 'Enter e-mail and password.',
    'login.error_bad'   => 'Wrong e-mail / password combination.',

    // account
    'account.title'     => 'My Account',
    'account.greeting'  => 'Hello, {name}!',
    'account.my_orders' => 'My orders ({n})',
    'account.logout'    => 'Sign out',
    'orders.title'      => 'My orders',
    'orders.col_number' => 'Number',
    'orders.col_date'   => 'Date',
    'orders.col_items'  => 'Items',
    'orders.col_sum'    => 'Total',
    'orders.col_status' => 'Status',
    'orders.none'       => 'You have no orders yet.',
    'orders.back'       => '← Back to account',

    // 404
    'error.404_title' => '404 — Page not found',
    'error.404_text'  => 'The requested address {path} does not exist.',
    'error.to_home'   => 'Back to home',

    // terms
    'terms.title'     => 'Delivery & Payment',
    'terms.intro'     => 'This is a demo terms page. A real store would list payment options, delivery methods and timelines, warranties and contacts here.',
    'terms.pay_head'  => 'Payment options',
    'terms.pay_cash'  => 'Cash on delivery',
    'terms.pay_card'  => 'Bank card',
    'terms.pay_wire'  => 'Bank transfer',
    'terms.ship_head' => 'Delivery',
    'terms.ship_text' => 'Free local delivery on orders over ₽5,000.',

    // feedback
    'feedback.title'   => 'Contact',
    'feedback.note'    => 'Demo form. Fields are validated in the browser; submitting does nothing.',
    'feedback.name'    => 'Name',
    'feedback.message' => 'Message',
    'feedback.submit'  => 'Send',
    'feedback.sent'    => 'Thank you! This is a demo form — the message is not stored anywhere.',

    // credits
    'credits.title'        => 'Image credits',
    'credits.intro'        => 'This is a non-commercial educational portfolio project. Product photos are sourced from Wikimedia Commons and used under their free licenses (CC BY-SA, CC BY, CC0, public domain). Brand logos are trademarks of their respective owners and are shown for identification purposes only.',
    'credits.logos_head'   => 'Brand logos',
    'credits.logos_note'   => 'Trademarks of their respective owners.',
    'credits.photos_head'  => 'Product photos',
    'credits.photos_note'  => 'Source: Wikimedia Commons.',
    'credits.col_brand'    => 'Brand',
    'credits.col_product'  => 'Product',
    'credits.col_author'   => 'Author',
    'credits.col_license'  => 'License',
    'credits.col_source'   => 'Source',

    // about
    'about.title' => 'About',
    'about.p1'    => 'This is a portfolio project — a modernized rewrite of a home-appliance store frontend.',
    'about.p2'    => 'The original code ran on a hand-rolled PHP engine with direct MySQL and MongoDB access. In the demo, all data is stored in JSON files under storage/data/, authentication uses bcrypt, routing uses FastRoute, and logging uses Monolog.',
    'about.p3'    => 'The goal of the project is to demonstrate a code refactor: removing infrastructure dependencies, moving to PSR-4, and a clean structure of actions, repositories and services.',
    'about.demo_head'  => 'What the project demonstrates',
    'about.demo_1'     => 'a product catalog with categories, brands and filters;',
    'about.demo_2'     => 'a product page with specifications and reviews;',
    'about.demo_3'     => 'a cart and checkout (orders are written to JSON);',
    'about.demo_4'     => 'bcrypt-password authentication with demo users;',
    'about.demo_5'     => 'catalog search.',
    'about.stack_head' => 'Stack',
    'about.stack_route' => 'nikic/fast-route — routing',
    'about.stack_log'   => 'monolog/monolog — logging',
    'about.stack_uuid'  => 'ramsey/uuid — UUID generation',
    'about.stack_env'   => 'vlucas/phpdotenv — configuration',
    'about.stack_tpl'   => 'a simple {…}-substitution template engine',

    // plural words
    'word.items.one'    => 'item',
    'word.items.few'    => 'items',
    'word.items.many'   => 'items',
    'word.products'     => 'products',
    'word.results.one'  => 'result',
    'word.results.few'  => 'results',
    'word.results.many' => 'results',
];
