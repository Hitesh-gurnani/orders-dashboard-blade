<?php

declare(strict_types=1);

/**
 * Three URL builders, loaded via composer's autoload.files.
 *
 * Laravel's url()/route() helpers are not available here (see README: this is
 * illuminate/view standing alone, not the framework), so links are built from
 * the GET state explicitly. That is a boundary, not a gap -- the URL IS the
 * application state on this page, and building it in one place is what keeps
 * every filter, sort and page link agreeing about that.
 */

if (! function_exists('orders_url')) {
    /**
     * @param array<string, scalar|null> $query
     */
    function orders_url(array $query): string
    {
        // Drop defaults so the address bar stays readable: '/' rather than
        // '/?status=all&sort=placed&dir=desc&page=1'.
        //
        // theme and density are deliberately NOT in this list. Dropping
        // theme=auto would make "I choose auto" and "I said nothing" the same
        // URL, and the cookie would keep serving the old override forever. They
        // cost a query parameter once the control is touched; the alternative
        // is a toggle with a dead position.
        $defaults = ['status' => 'all', 'sort' => 'placed', 'dir' => 'desc', 'page' => 1, 'q' => ''];

        $query = array_filter(
            $query,
            static fn ($value, string $key): bool => $value !== null
                && $value !== ''
                && (! array_key_exists($key, $defaults) || $value !== $defaults[$key]),
            ARRAY_FILTER_USE_BOTH
        );

        ksort($query);

        return $query === [] ? '/' : '/?' . http_build_query($query);
    }
}

if (! function_exists('orders_sort_url')) {
    /**
     * Toggle direction when re-clicking the active column; otherwise start each
     * column in the direction a person actually wants first -- newest dates and
     * largest amounts at the top, names from A.
     *
     * @param array{column: string, dir: string, query: array<string, scalar|null>} $state
     */
    function orders_sort_url(array $state, string $column): string
    {
        $descendingFirst = ['placed', 'total', 'items'];

        $dir = $state['column'] === $column
            ? ($state['dir'] === 'asc' ? 'desc' : 'asc')
            : (in_array($column, $descendingFirst, true) ? 'desc' : 'asc');

        // Sorting always returns to page 1: holding page 3 while the underlying
        // order changes shows you rows you never asked for.
        return orders_url(['sort' => $column, 'dir' => $dir] + ['page' => 1] + $state['query']);
    }
}

if (! function_exists('orders_url_without')) {
    /**
     * The empty state's removable filter tokens: this URL minus one parameter.
     *
     * @param array<string, scalar|null> $query
     */
    function orders_url_without(array $query, string ...$keys): string
    {
        foreach ($keys as $key) {
            unset($query[$key]);
        }

        unset($query['page']);

        return orders_url($query);
    }
}
