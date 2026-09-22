<?php

declare(strict_types=1);

use App\Data\OrderRepository;
use App\Support\OrderStatus;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

require __DIR__ . '/../vendor/autoload.php';

/*
|------------------------------------------------------------------------------
| Blade, standing on its own
|------------------------------------------------------------------------------
|
| This is what Laravel's ViewServiceProvider does on your behalf. It is written
| out here rather than hidden behind a wrapper package because it is the most
| interesting non-template file in the project -- and because every wrapper I
| looked at omits step 4, which is the exact reason people conclude that <x-...>
| component syntax "does not work outside Laravel". It does. You just have to
| answer a question the framework normally answers for you.
|
| The order below is load-bearing.
*/

// 1. The container must be the SHARED instance before anything else touches it.
//    Container::getInstance() is `static::$instance ??= new static`, so skipping
//    setInstance() hands the compiler a different, empty container and every
//    component tag dies resolving the view factory.
$container = new Container;
Container::setInstance($container);

$files = new Filesystem;
$views = __DIR__ . '/../resources/views';
$compiled = __DIR__ . '/../storage/framework/views';

$files->ensureDirectoryExists($compiled);

// 2. An empty compiled path throws, so it is created above rather than assumed.
$blade = new BladeCompiler($files, $compiled);

$engines = new EngineResolver;
$engines->register('blade', fn () => new CompilerEngine($blade));
$engines->register('php', fn () => new PhpEngine($files));

$factory = new Factory($engines, new FileViewFinder($files, [$views]), new Dispatcher($container));
$factory->setContainer($container);

// 3. Bound under all three names Blade looks it up by, because it uses a
//    different one in each place: the literal string 'view' at component render
//    time, the contract from the tag compiler, and the concrete class elsewhere.
//    Note the argument order -- alias($abstract, $alias) backwards is a silent
//    no-op until something renders.
$container->instance('view', $factory);
$container->alias('view', ViewFactoryContract::class);
$container->alias('view', Factory::class);

// 4. THE NON-OBVIOUS ONE, and the whole reason this file is worth reading.
//    ComponentTagCompiler::componentClass() resolves component names in a fixed
//    order, and before it ever looks for an anonymous template it calls
//    Container::getInstance()->make(Application::class)->getNamespace() to guess
//    a class name. In a bare container that contract has no binding, so the
//    first <x-...> tag on the page throws BindingResolutionException. All it
//    actually wants is a root namespace string. The guessed class (App\View\
//    Components\Stat and friends) deliberately does not exist, so resolution
//    falls through to resources/views/components/ -- which is where every
//    anonymous component in this project lives.
$container->bind(ApplicationContract::class, fn () => new class {
    public function getNamespace(): string
    {
        return 'App\\';
    }
});

// 5. The one class-backed component. A plain array assignment on the compiler:
//    no service provider, no container, and nothing resolved until a tag uses it.
$blade->component(\App\View\Components\Badge::class, 'badge');

// Anonymous components need NO registration at all: the tag compiler always
// tries `components.<name>` first, so <x-stat> finds components/stat.blade.php
// and <x-table.row> finds components/table/row.blade.php. anonymousComponentPath()
// is deliberately not called -- it eagerly resolves the view factory at bootstrap
// and buys nothing here.

/*
|------------------------------------------------------------------------------
| Request
|------------------------------------------------------------------------------
|
| All state is GET and every value is whitelisted before it is used. The URL is
| the application state: change a filter and the address bar says what you did,
| and the result is shareable.
*/

$pick = static function (string $key, array $allowed, string $fallback): string {
    $value = isset($_GET[$key]) && is_string($_GET[$key]) ? $_GET[$key] : '';

    return in_array($value, $allowed, true) ? $value : $fallback;
};

// Theme and density are echoed onto <html> server-side, so there is no blocking
// inline script and no flash of the wrong theme. The cookie only remembers what
// the URL last said.
$theme = isset($_GET['theme'])
    ? $pick('theme', ['auto', 'light', 'dark'], 'auto')
    : (in_array($_COOKIE['theme'] ?? '', ['light', 'dark'], true) ? $_COOKIE['theme'] : 'auto');

$density = isset($_GET['density'])
    ? $pick('density', ['cozy', 'compact'], 'cozy')
    : (($_COOKIE['density'] ?? '') === 'compact' ? 'compact' : 'cozy');

if (isset($_GET['theme'])) {
    setcookie('theme', $theme, ['expires' => 0, 'path' => '/', 'samesite' => 'Lax']);
}

if (isset($_GET['density'])) {
    setcookie('density', $density, ['expires' => 0, 'path' => '/', 'samesite' => 'Lax']);
}

$status = $pick('status', ['all', ...OrderStatus::values()], 'all');
$sort = $pick('sort', OrderRepository::SORTABLE, 'placed');
$dir = $pick('dir', ['asc', 'desc'], 'desc');
$search = mb_substr(trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : ''), 0, 64);
$page = max(1, (int) ($_GET['page'] ?? 1));

$repository = new OrderRepository;
$result = $repository->page($status, $search, $sort, $dir, $page);
$counts = $repository->statusCounts($search);

// The query state every link on the page is rebuilt from.
//
// theme and density are carried only when they are NOT at their default, so a
// first visit has clean URLs. They cannot be dropped by orders_url() itself:
// "I chose auto" and "I said nothing" would become the same URL, and the cookie
// would go on serving the old override forever -- a toggle with a dead position.
$query = array_filter([
    'status' => $status,
    'q' => $search,
    'sort' => $sort,
    'dir' => $dir,
    'theme' => $theme === 'auto' ? '' : $theme,
    'density' => $density === 'cozy' ? '' : $density,
], static fn ($value): bool => $value !== '');

// Just the chrome: what a "clear everything" link should preserve. Resetting
// the filters should not also throw away the theme you picked.
$chrome = array_intersect_key($query, ['theme' => null, 'density' => null]);

$labels = [
    'all' => 'All',
    'fulfilled' => 'Fulfilled',
    'processing' => 'Processing',
    'refunded' => 'Refunded',
    'cancelled' => 'Cancelled',
];

$chips = [];

foreach ($labels as $key => $label) {
    $chips[] = [
        'label' => $label,
        'count' => $counts[$key] ?? 0,
        'current' => $status === $key,
        'href' => orders_url(['status' => $key, 'page' => 1] + $query),
    ];
}

// The empty state is a diff of the query, so each constraint is removable on its
// own. index.php decides which copy applies; the template holds no branch.
$tokens = [];

// Two phrasings of the same constraint, on purpose. The token is a key/value
// pair because it sits in a row of them and has to be scannable; the sentence
// is prose because it is a sentence. Reusing the token text in the heading
// gives you "No orders match status: cancelled and search: kepler", which is
// what a form would say, not a person.
$phrases = [];

if ($status !== 'all') {
    $tokens[] = [
        'label' => 'status: ' . strtolower($labels[$status]),
        'removeHref' => orders_url_without($query, 'status'),
        'removeLabel' => 'Remove the ' . strtolower($labels[$status]) . ' status filter',
    ];

    $phrases[] = $labels[$status];
}

if ($search !== '') {
    $tokens[] = [
        'label' => 'search: “' . $search . '”',
        'removeHref' => orders_url_without($query, 'q'),
        'removeLabel' => 'Remove the search for ' . $search,
    ];

    $phrases[] = '“' . $search . '”';
}

echo $factory->make('orders.index', [
    'theme' => $theme,
    'density' => $density,
    'period' => '1–22 Sep 2026',
    'query' => $query,
    'search' => $search,
    'nav' => [
        ['key' => 'overview', 'label' => 'Overview', 'href' => '/'],
        ['key' => 'orders', 'label' => 'Orders', 'href' => orders_url($chrome), 'count' => $counts['all']],
        ['key' => 'products', 'label' => 'Products', 'href' => '/'],
        ['key' => 'customers', 'label' => 'Customers', 'href' => '/'],
        ['key' => 'discounts', 'label' => 'Discounts', 'href' => '/'],
        ['key' => 'reports', 'label' => 'Reports', 'href' => '/'],
        ['key' => 'settings', 'label' => 'Settings', 'href' => '/'],
    ],
    'metrics' => $repository->metrics(),
    'chips' => $chips,
    'state' => [
        'column' => $sort,
        'dir' => $dir,
        'query' => $query,
        'label' => $sort . ', ' . ($dir === 'asc' ? 'ascending' : 'descending'),
    ],
    'rows' => $result['rows'],
    'matched' => $result['total'],
    'total' => $result['total'],
    'pages' => $result['pages'],
    'page' => $result['page'],
    'from' => $result['from'],
    'to' => $result['to'],
    'netTotal' => $repository->netTotal($result['matched']),
    'filterSummary' => $phrases === [] ? '' : ', filtered by ' . implode(' and ', $phrases),
    'carry' => array_diff_key($query, ['q' => null]),
    'empty' => $tokens === []
        ? ['title' => 'No orders yet.', 'body' => 'They will appear here as they come in.', 'filters' => [], 'clear' => null]
        : ['title' => 'No orders match ' . implode(' and ', $phrases) . '.', 'body' => null, 'filters' => $tokens, 'clear' => orders_url($chrome)],
])->render();
