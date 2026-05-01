# Homy — e-store demo

Portfolio refactor of a household-appliances e-commerce storefront.

The original codebase was a custom PHP 5.x engine with MySQL + MongoDB,
home-grown autoloader, URL parser, template engine, SMS gateway, social-auth,
and Yandex.Market YML feed. This rewrite preserves the user-visible storefront
(homepage, catalog, product, cart, checkout, account) and drops everything
infrastructure-heavy. **No database server is required.** All read data lives
in JSON files under `storage/data/`; orders the user places in the demo are
written to `storage/runtime/orders.json`.

## Quick start

```bash
# 1) install dependencies
composer install

# 2) run the dev server
composer serve
# → http://localhost:8080

# (alternative)
php -S localhost:8080 -t public public/router.php
```

That's it — no MySQL, no MongoDB, no Redis, no node toolchain.

Demo accounts (passwords are bcrypt hashes in [users.json](storage/data/users.json)):

| Login              | Password |
|--------------------|----------|
| `demo@homy.local`  | `demo`   |
| `admin@homy.local` | `admin`  |

## Project layout

```
public/             ← only entry point + static assets
  index.php         ← front controller
  .htaccess         ← Apache rewrite to front controller
  router.php        ← router for `php -S` dev server
  css/, js/, img/   ← static assets
src/
  App.php           ← bootstrap, manual DI, route table
  Action/           ← one class per page, single __invoke()
  Domain/           ← Product, Category, Brand, User, Order DTOs
  Repository/       ← ProductRepository, CategoryRepository, …
  Service/          ← CartService, AuthService, PriceFormatter, Slugify, RussianLocale
  Storage/          ← JsonStore (atomic JSON-on-disk reads/writes)
  Template/         ← TemplateEngine ({key} substitution) + LayoutRenderer
  Http/             ← Request, Response
  Support/          ← Session helper
storage/
  data/             ← versioned demo fixtures (categories, brands, products, reviews, users)
  runtime/          ← writable: orders, app.log
templates/          ← .tpl files (HTML fragments with {key} placeholders)
```

## What this codebase demonstrates

- **PSR-4 + Composer** instead of a hand-rolled autoloader.
- **FastRoute** instead of an `explode('/', $path)` URL parser.
- **Single-action controllers** instead of fat module classes mixing SQL,
  HTML and business logic in one file.
- **Repositories over a JSON store** instead of `mysql_*` calls scattered
  across every controller.
- **Modern auth**: `password_hash` / `password_verify`, hardened session
  cookies, no MD5 + rolled-your-own-salt.
- **Monolog** for app logging instead of an `error_log` table in MySQL.
- **Manual constructor injection** in [App.php](src/App.php) — visible,
  inspectable wiring, no DI container magic for an 8-service app.

## Vendor packages used (and why)

| Package                | Replaces                                      |
|------------------------|-----------------------------------------------|
| `nikic/fast-route`     | URL parser inside the original `core` class   |
| `monolog/monolog`      | `error_log()` + a `logs` MySQL table          |
| `ramsey/uuid`          | `mt_rand()`-based UUID generator              |
| `vlucas/phpdotenv`     | Hardcoded credentials in `cfg.class.php`      |
| `symfony/var-dumper`   | A 487-line homegrown `dBug` debug helper      |

Deliberately **not** introduced: a full framework (Slim/Symfony/Laravel),
a templating engine (Twig/Plates), or a DI container. Each would obscure
the architecture story without paying for itself in a 10-page demo.

## What was removed from the legacy codebase

- `class/db/mysql/mysqlcrud.class.php` — used the removed-in-PHP-7
  `mysql_*` extension; replaced by JSON repositories.
- `class/db/mongo/mongo.class.php`, `class/tmp/mongocrud.class.php` — used
  the legacy `MongoClient`; the only caller already had its lookup commented
  out.
- `class/tmp/master.class.php` — 865-line god-class; split into
  `CartService`, `AuthService`, `Slugify`, `BreadcrumbBuilder` (in
  `LayoutRenderer`).
- `bot.php` (Telegram), `class/sms/`, `class/yml/`, `class/ajax/csv*`,
  `class/ajax/photoupload.php`, `extauth.php` (social-auth), `cc.php`
  (admin), `class/mail/sendmailSMTP` — out-of-scope for a portfolio demo.
- `class/debug/dBug.class.php`, `class/test/`, `class/helper/git`,
  `class/helper/info`, `class/vendor/parseCSV`, `class/vendor/todoist`,
  `class/vendor/php_rutils` — legacy helpers replaced by stdlib or
  vendor packages.
- All hardcoded credentials from `cfg.class.php` and `mongo.class.php`.

## Storage layer

`storage/data/*.json` is the read-only demo dataset (`categories.json`,
`brands.json`, `products.json`, `reviews.json`, `users.json`). It is
versioned in git and inspectable by hand.

`storage/runtime/*.json` is anything the running app writes. Today that's
just `orders.json` (and `app.log` from Monolog). The whole directory is
gitignored except for `.gitkeep`.

[`JsonStore`](src/Storage/JsonStore.php) does atomic writes (write to a
temp file, `flock(LOCK_EX)`, `rename`). Reads are cached for the lifetime
of the request.

To reset the demo, delete `storage/runtime/orders.json`.

## Sanity check

```bash
# lint
find src public -name '*.php' -print0 | xargs -0 -n1 php -l

# smoke
composer serve &
for url in / /category/ /category/smartphones/ /product/1001-/ \
          /cart/ /login/ /feedback/ /terms/ /about/; do
   curl -s -o /dev/null -w "%{http_code} %s\n" "${url}" "http://localhost:8080${url}"
done
```

## License

MIT.
