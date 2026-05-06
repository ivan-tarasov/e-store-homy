<?php

declare(strict_types=1);

namespace App;

use App\Action\Account\AccountAction;
use App\Action\Account\OrdersAction;
use App\Action\Admin\DashboardAction as AdminDashboardAction;
use App\Action\Admin\OrdersAction as AdminOrdersAction;
use App\Action\Admin\OrderShowAction as AdminOrderShowAction;
use App\Action\Admin\ProductDeleteAction as AdminProductDeleteAction;
use App\Action\Admin\ProductEditAction as AdminProductEditAction;
use App\Action\Admin\ProductSaveAction as AdminProductSaveAction;
use App\Action\Admin\ProductsAction as AdminProductsAction;
use App\Action\Admin\UsersAction as AdminUsersAction;
use App\Action\Auth\LoginAction;
use App\Action\Auth\LogoutAction;
use App\Action\Auth\SubmitLoginAction;
use App\Action\Cart\AddToCartAction;
use App\Action\Cart\CartAction;
use App\Action\Cart\RemoveFromCartAction;
use App\Action\Cart\UpdateCartAction;
use App\Action\Category\CategoryIndexAction;
use App\Action\Category\CategoryShowAction;
use App\Action\Checkout\CheckoutAction;
use App\Action\Checkout\SubmitCheckoutAction;
use App\Action\Errors\NotFoundAction;
use App\Action\Home\HomeAction;
use App\Action\Pages\AboutAction;
use App\Action\Pages\FeedbackAction;
use App\Action\Pages\TermsAction;
use App\Action\Product\ProductShowAction;
use App\Action\Search\SearchAction;
use App\Action\Search\SearchResultsAction;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\CartService;
use App\Service\PriceFormatter;
use App\Service\ProductCardRenderer;
use App\Service\RussianLocale;
use App\Service\Slugify;
use App\Storage\JsonStore;
use App\Support\Session;
use App\Template\LayoutRenderer;
use App\Template\TemplateEngine;
use FastRoute\Dispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;

use function FastRoute\simpleDispatcher;

final class App
{
    public function __construct(
        public readonly string $rootDir,
        public readonly LoggerInterface $logger,
        public readonly Session $session,
        public readonly TemplateEngine $tpl,
        public readonly LayoutRenderer $layout,
        public readonly Dispatcher $dispatcher,
        public readonly JsonStore $store,
        public readonly CategoryRepository $categories,
        public readonly BrandRepository $brands,
        public readonly ProductRepository $products,
        public readonly ReviewRepository $reviews,
        public readonly UserRepository $users,
        public readonly OrderRepository $orders,
        public readonly AuthService $auth,
        public readonly CartService $cart,
        public readonly PriceFormatter $price,
        public readonly Slugify $slugify,
        public readonly RussianLocale $locale,
        public readonly ProductCardRenderer $cardRenderer,
    ) {
    }

    public static function bootstrap(string $rootDir): self
    {
        $logsDir = $rootDir . '/storage/runtime';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0o775, true);
        }

        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($logsDir . '/app.log', Logger::DEBUG));

        $store = new JsonStore(
            dataDir: $rootDir . '/storage/data',
            runtimeDir: $rootDir . '/storage/runtime',
        );

        $session = new Session();
        $session->start();

        $tpl = new TemplateEngine($rootDir . '/templates');

        $categoriesRepo = new CategoryRepository($store);
        $brandsRepo = new BrandRepository($store);
        $productsRepo = new ProductRepository($store, $categoriesRepo);
        $reviewsRepo = new ReviewRepository($store);
        $usersRepo = new UserRepository($store);
        $ordersRepo = new OrderRepository($store);

        $auth = new AuthService($session, $usersRepo);
        $cart = new CartService($session, $productsRepo);
        $price = new PriceFormatter();
        $slugify = new Slugify();
        $locale = new RussianLocale();
        $cardRenderer = new ProductCardRenderer($brandsRepo, $categoriesRepo, $slugify, $price);

        $layout = new LayoutRenderer(
            tpl: $tpl,
            categories: $categoriesRepo,
            auth: $auth,
            cart: $cart,
            price: $price,
            slugify: $slugify,
            shopName: 'Homy.su — каталог бытовой техники',
            shopPhone: '8 (4712) <strong>220-580</strong>',
            shopEmail: 'demo@homy.local',
            shopAddress: '305000, г. Курск, Элеваторный проезд, 14',
        );

        $dispatcher = self::buildDispatcher();

        return new self(
            rootDir: $rootDir,
            logger: $logger,
            session: $session,
            tpl: $tpl,
            layout: $layout,
            dispatcher: $dispatcher,
            store: $store,
            categories: $categoriesRepo,
            brands: $brandsRepo,
            products: $productsRepo,
            reviews: $reviewsRepo,
            users: $usersRepo,
            orders: $ordersRepo,
            auth: $auth,
            cart: $cart,
            price: $price,
            slugify: $slugify,
            locale: $locale,
            cardRenderer: $cardRenderer,
        );
    }

    public function handle(Request $request): Response
    {
        try {
            $route = $this->dispatcher->dispatch($request->method, $request->path);
            return match ($route[0]) {
                Dispatcher::FOUND => $this->callAction($route[1], $request, $route[2]),
                Dispatcher::METHOD_NOT_ALLOWED => Response::html('Method not allowed', 405),
                default => $this->callAction(NotFoundAction::class, $request, []),
            };
        } catch (Throwable $e) {
            $this->logger->error('Unhandled exception', ['exception' => $e]);
            return Response::html(
                $this->layout->render(
                    '<section class="container"><h1>500 — Server error</h1>'
                    . '<p>Что-то пошло не так. В демо-версии исключения логируются в storage/runtime/app.log.</p></section>',
                    new \App\Template\PageMeta('500 — Server error'),
                ),
                500,
            );
        }
    }

    /** @param array<string, string> $vars */
    private function callAction(string $actionClass, Request $request, array $vars): Response
    {
        $action = $this->buildAction($actionClass);
        return $action($request, $vars);
    }

    private function buildAction(string $class): object
    {
        return match ($class) {
            HomeAction::class => new HomeAction($this->layout, $this->tpl, $this->products, $this->brands, $this->cardRenderer),
            CategoryIndexAction::class => new CategoryIndexAction($this->layout, $this->tpl, $this->categories, $this->products, $this->locale),
            CategoryShowAction::class => new CategoryShowAction($this->layout, $this->tpl, $this->categories, $this->brands, $this->products, $this->cardRenderer),
            ProductShowAction::class => new ProductShowAction($this->layout, $this->tpl, $this->products, $this->categories, $this->brands, $this->reviews, $this->price, $this->cardRenderer, $this->locale, $this->cart),
            CartAction::class => new CartAction($this->layout, $this->tpl, $this->cart, $this->price, $this->slugify),
            AddToCartAction::class => new AddToCartAction($this->cart),
            UpdateCartAction::class => new UpdateCartAction($this->cart, $this->price),
            RemoveFromCartAction::class => new RemoveFromCartAction($this->cart),
            CheckoutAction::class => new CheckoutAction($this->layout, $this->tpl, $this->cart, $this->price, $this->slugify, $this->auth, $this->session),
            SubmitCheckoutAction::class => new SubmitCheckoutAction($this->cart, $this->orders, $this->auth, $this->session, $this->layout),
            LoginAction::class => new LoginAction($this->layout, $this->tpl, $this->auth, $this->session),
            SubmitLoginAction::class => new SubmitLoginAction($this->auth, $this->session),
            LogoutAction::class => new LogoutAction($this->auth),
            AccountAction::class => new AccountAction($this->layout, $this->tpl, $this->auth, $this->orders),
            OrdersAction::class => new OrdersAction($this->layout, $this->tpl, $this->auth, $this->orders, $this->locale),
            AboutAction::class => new AboutAction($this->layout, $this->tpl),
            TermsAction::class => new TermsAction($this->layout, $this->tpl),
            FeedbackAction::class => new FeedbackAction($this->layout, $this->tpl, $this->session),
            SearchAction::class => new SearchAction($this->products, $this->categories, $this->brands, $this->slugify),
            SearchResultsAction::class => new SearchResultsAction($this->layout, $this->products, $this->cardRenderer),
            AdminDashboardAction::class => new AdminDashboardAction($this->layout, $this->auth, $this->products, $this->categories, $this->brands, $this->users, $this->orders, $this->price, $this->locale, $this->tpl),
            AdminOrdersAction::class => new AdminOrdersAction($this->layout, $this->auth, $this->orders, $this->price, $this->locale),
            AdminOrderShowAction::class => new AdminOrderShowAction($this->layout, $this->auth, $this->orders, $this->users, $this->price, $this->locale),
            AdminProductsAction::class => new AdminProductsAction($this->layout, $this->auth, $this->products, $this->categories, $this->brands, $this->price, $this->slugify, $this->tpl),
            AdminProductEditAction::class => new AdminProductEditAction($this->layout, $this->auth, $this->products, $this->categories, $this->brands),
            AdminProductSaveAction::class => new AdminProductSaveAction($this->auth, $this->products, $this->session),
            AdminProductDeleteAction::class => new AdminProductDeleteAction($this->auth, $this->products),
            AdminUsersAction::class => new AdminUsersAction($this->layout, $this->auth, $this->users, $this->orders),
            NotFoundAction::class => new NotFoundAction($this->layout),
            default => throw new \RuntimeException('Unknown action: ' . $class),
        };
    }

    private static function buildDispatcher(): Dispatcher
    {
        return simpleDispatcher(static function (\FastRoute\RouteCollector $r): void {
            $r->addRoute('GET', '/', HomeAction::class);

            $r->addRoute('GET', '/category', CategoryIndexAction::class);
            $r->addRoute('GET', '/category/', CategoryIndexAction::class);
            $r->addRoute('GET', '/category/{slug}', CategoryShowAction::class);
            $r->addRoute('GET', '/category/{slug}/', CategoryShowAction::class);
            $r->addRoute('GET', '/category/{slug}/brand/{brand}', CategoryShowAction::class);
            $r->addRoute('GET', '/category/{slug}/brand/{brand}/', CategoryShowAction::class);

            $r->addRoute('GET', '/product/{idslug}', ProductShowAction::class);
            $r->addRoute('GET', '/product/{idslug}/', ProductShowAction::class);

            $r->addRoute('GET', '/cart', CartAction::class);
            $r->addRoute('GET', '/cart/', CartAction::class);
            $r->addRoute('POST', '/cart/add', AddToCartAction::class);
            $r->addRoute('POST', '/cart/update', UpdateCartAction::class);
            $r->addRoute('POST', '/cart/remove', RemoveFromCartAction::class);

            $r->addRoute('GET', '/checkout', CheckoutAction::class);
            $r->addRoute('GET', '/checkout/', CheckoutAction::class);
            $r->addRoute('POST', '/checkout', SubmitCheckoutAction::class);
            $r->addRoute('POST', '/checkout/', SubmitCheckoutAction::class);

            $r->addRoute('GET', '/login', LoginAction::class);
            $r->addRoute('GET', '/login/', LoginAction::class);
            $r->addRoute('POST', '/login', SubmitLoginAction::class);
            $r->addRoute('POST', '/login/', SubmitLoginAction::class);
            $r->addRoute('GET', '/logout', LogoutAction::class);
            $r->addRoute('GET', '/logout/', LogoutAction::class);

            $r->addRoute('GET', '/my', AccountAction::class);
            $r->addRoute('GET', '/my/', AccountAction::class);
            $r->addRoute('GET', '/my/orders', OrdersAction::class);
            $r->addRoute('GET', '/my/orders/', OrdersAction::class);

            $r->addRoute('GET', '/about', AboutAction::class);
            $r->addRoute('GET', '/about/', AboutAction::class);
            $r->addRoute('GET', '/terms', TermsAction::class);
            $r->addRoute('GET', '/terms/', TermsAction::class);
            $r->addRoute('GET', '/feedback', FeedbackAction::class);
            $r->addRoute('GET', '/feedback/', FeedbackAction::class);
            $r->addRoute('POST', '/feedback', FeedbackAction::class);
            $r->addRoute('POST', '/feedback/', FeedbackAction::class);

            $r->addRoute('GET', '/search', SearchAction::class);
            $r->addRoute('GET', '/search/', SearchResultsAction::class);

            $r->addRoute('GET', '/admin', AdminDashboardAction::class);
            $r->addRoute('GET', '/admin/', AdminDashboardAction::class);
            $r->addRoute('GET', '/admin/orders', AdminOrdersAction::class);
            $r->addRoute('GET', '/admin/orders/', AdminOrdersAction::class);
            $r->addRoute('GET', '/admin/orders/{id}', AdminOrderShowAction::class);
            $r->addRoute('GET', '/admin/products', AdminProductsAction::class);
            $r->addRoute('GET', '/admin/products/', AdminProductsAction::class);
            $r->addRoute('GET', '/admin/products/new', AdminProductEditAction::class);
            $r->addRoute('GET', '/admin/products/{id}/edit', AdminProductEditAction::class);
            $r->addRoute('POST', '/admin/products/save', AdminProductSaveAction::class);
            $r->addRoute('POST', '/admin/products/{id}/delete', AdminProductDeleteAction::class);
            $r->addRoute('GET', '/admin/users', AdminUsersAction::class);
            $r->addRoute('GET', '/admin/users/', AdminUsersAction::class);
        });
    }
}
