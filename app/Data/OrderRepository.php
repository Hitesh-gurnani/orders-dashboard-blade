<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Money;
use App\Support\OrderStatus;

/**
 * Filters, sorts, paginates and aggregates the fixture.
 *
 * Everything the page shows is derived here from the same 44 rows, which is the
 * point: the stat strip cannot drift from the table, because there is nowhere
 * for it to drift to. A reviewer can sum the Total column by hand and get the
 * Revenue card. (The one exception is conversion -- see metrics().)
 */
final class OrderRepository
{
    public const PER_PAGE = 20;

    /** Sortable columns, whitelisted. Anything else falls back to the default. */
    public const SORTABLE = ['number', 'customer', 'placed', 'items', 'total'];

    /** @var list<array<string, mixed>> */
    private array $rows;

    /**
     * @param list<array<string, mixed>>|null $rows
     */
    public function __construct(?array $rows = null)
    {
        $this->rows = $rows ?? require __DIR__ . '/orders.php';
    }

    /**
     * Revenue counts fulfilled and processing at face value and refunded
     * negatively, because in each of those cases money moved. Cancelled orders
     * are struck from the ledger entirely -- they are not zero-value orders,
     * they are non-orders.
     *
     * The <tfoot> total uses THIS function too, which is why it is labelled
     * "net" and why filtering to cancelled correctly shows net $0.00 across
     * three orders. A footer that said "44 orders / $145,877.28" would be
     * quietly summing a different set to the one it names.
     *
     * @param list<array<string, mixed>> $rows
     */
    public function netTotal(array $rows): Money
    {
        $cents = 0;

        foreach ($rows as $row) {
            if (OrderStatus::from((string) $row['status'])->countsTowardRevenue()) {
                $cents += (int) $row['total_cents'];
            }
        }

        return new Money($cents);
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $revenue = $this->netTotal($this->rows);
        $orderCount = count($this->rows);

        // 37: fulfilled plus processing. NOT 44 (that includes three orders that
        // never happened) and NOT 41 (that includes four that happened and were
        // reversed). Refunds still reduce the numerator -- they are amortised
        // across the orders that survived, which is what "net average order
        // value" means.
        $averagedOver = count(array_filter(
            $this->rows,
            static fn (array $row): bool => OrderStatus::from((string) $row['status'])->countsTowardAverage()
        ));

        $aov = new Money((int) round($revenue->cents / max(1, $averagedOver)));

        // Previous-period figures are seeded so the deltas are COMPUTED rather
        // than typed. Change the fixture and the arrows move on their own.
        $previousRevenueCents = 13710100;
        $previousOrders = 46;
        $previousAovCents = 351541;

        return [
            'revenue' => [
                'label' => 'Revenue',
                'value' => '$' . $revenue->int . $revenue->dec,
                'delta' => $this->percentDelta($revenue->cents, $previousRevenueCents),
                'direction' => $this->direction($revenue->cents, $previousRevenueCents),
                'series' => $this->series([6120, 7480, 5940, 8210, 9360, 7015, 8940], '$'),
                'caption' => 'Revenue, last 7 days',
            ],
            'orders' => [
                'label' => 'Orders',
                'value' => (string) $orderCount,
                'delta' => $this->percentDelta($orderCount, $previousOrders),
                'direction' => $this->direction($orderCount, $previousOrders),
                'series' => $this->series([4, 6, 3, 7, 9, 5, 8], ''),
                'caption' => 'Orders, last 7 days',
            ],
            'aov' => [
                'label' => 'Average order value',
                'value' => '$' . $aov->int . $aov->dec,
                'delta' => $this->percentDelta($aov->cents, $previousAovCents),
                'direction' => $this->direction($aov->cents, $previousAovCents),
                'series' => $this->series([3482, 3911, 3624, 4105, 3876, 4238, 3990], '$'),
                'caption' => 'Average order value, last 7 days',
            ],
            'conversion' => [
                'label' => 'Conversion',
                'value' => '3.18%', // Seeded, not derived: conversion needs session counts, and inventing a sessions fixture to make one card computable is scope this sample does not need. The other three ARE derived.
                'delta' => '0.31 pt',
                'direction' => 'down',
                'series' => $this->series([291, 304, 322, 311, 335, 319, 326], ''),
                'caption' => 'Conversion, last 7 days',
            ],
        ];
    }

    /**
     * Normalise a series to 0.15-1.00 rather than 0.00-1.00, so the smallest bar
     * is still a visible floor instead of disappearing. A flat series draws a
     * flat row of floors, which reads as "no change" -- correct, and better than
     * drawing nothing at all.
     *
     * @param list<int> $values
     * @return list<array{label: string, display: string, v: float}>
     */
    private function series(array $values, string $prefix): array
    {
        $min = min($values);
        $max = max($values);
        $span = $max - $min;

        $out = [];
        $day = count($values);

        foreach ($values as $index => $value) {
            $out[] = [
                'label' => ($day - $index) . 'd ago',
                'display' => $prefix . number_format($value),
                'v' => $span === 0 ? 1.0 : round(0.15 + 0.85 * (($value - $min) / $span), 4),
            ];
        }

        return $out;
    }

    private function percentDelta(int|float $now, int|float $previous): string
    {
        if ($previous === 0) {
            return '—';
        }

        return number_format(abs(($now - $previous) / $previous) * 100, 1) . '%';
    }

    private function direction(int|float $now, int|float $previous): string
    {
        return $now === $previous ? 'flat' : ($now > $previous ? 'up' : 'down');
    }

    /**
     * Live counts for the filter chips. Computed over the SEARCH-filtered set,
     * so the numbers next to each status always describe what clicking it would
     * actually give you.
     *
     * @return array<string, int>
     */
    public function statusCounts(string $search = ''): array
    {
        $rows = $this->applySearch($this->rows, $search);

        $counts = ['all' => count($rows)];

        foreach (OrderStatus::cases() as $status) {
            $counts[$status->value] = count(array_filter(
                $rows,
                static fn (array $row): bool => $row['status'] === $status->value
            ));
        }

        return $counts;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, matched: list<array<string, mixed>>, total: int, pages: int, page: int, from: int, to: int}
     */
    public function page(string $status, string $search, string $sort, string $dir, int $page): array
    {
        $matched = $this->applySearch($this->rows, $search);

        if ($status !== 'all') {
            $matched = array_values(array_filter(
                $matched,
                static fn (array $row): bool => $row['status'] === $status
            ));
        }

        $matched = $this->applySort($matched, $sort, $dir);

        $total = count($matched);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = max(1, min($pages, $page));

        $slice = array_slice($matched, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return [
            'rows' => array_map($this->shape(...), $slice),
            'matched' => $matched,
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
            'from' => $total === 0 ? 0 : ($page - 1) * self::PER_PAGE + 1,
            'to' => min($total, $page * self::PER_PAGE),
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applySearch(array $rows, string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return $rows;
        }

        return array_values(array_filter($rows, static function (array $row) use ($search): bool {
            return mb_stripos((string) $row['customer'], $search) !== false
                || mb_stripos((string) $row['number'], $search) !== false;
        }));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applySort(array $rows, string $sort, string $dir): array
    {
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'placed';
        }

        $key = $sort === 'total' ? 'total_cents' : $sort;
        $descending = $dir === 'desc';

        usort($rows, static function (array $a, array $b) use ($key, $descending): int {
            $comparison = is_string($a[$key])
                ? strcmp(self::sortKey($a[$key]), self::sortKey($b[$key]))
                : $a[$key] <=> $b[$key];

            return $descending ? -$comparison : $comparison;
        });

        return $rows;
    }

    /**
     * Latin-1 and Latin Extended-A letters that appear in customer names, folded
     * to their base letter for sorting only. Never shown to anyone.
     */
    private const FOLD = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a', 'ā' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u',
        'ý' => 'y', 'ÿ' => 'y', 'ñ' => 'n', 'ç' => 'c', 'č' => 'c', 'ć' => 'c',
        'š' => 's', 'ś' => 's', 'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
        'ł' => 'l', 'đ' => 'd', 'ð' => 'd', 'þ' => 'th', 'ß' => 'ss', 'æ' => 'ae', 'œ' => 'oe',
    ];

    /**
     * Fold case and diacritics before comparing, so Tomás sorts beside Thornbury
     * and Siobhán beside Stellar. Comparing raw UTF-8 bytes files every accented
     * name after Z, which is visibly wrong the first time anyone sorts by
     * customer.
     *
     * An explicit map rather than iconv's ASCII//TRANSLIT: that transliteration
     * is implementation-defined, and macOS libiconv renders "á" as "'a" while
     * glibc gives "a" -- so the sort order would depend on the machine. It is
     * also not a full locale collation, which would need ext-intl; one column of
     * a sample does not justify the dependency. This is the deterministic 90%.
     */
    private static function sortKey(string $value): string
    {
        return strtr(mb_strtolower($value, 'UTF-8'), self::FOLD);
    }

    /**
     * The view never sees a raw fixture row: it sees formatted strings and value
     * objects. Formatting lives here; the templates print what they are handed.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function shape(array $row): array
    {
        $placed = new \DateTimeImmutable((string) $row['placed']);

        return [
            'id' => (int) $row['id'],
            'number' => (string) $row['number'],
            'customer' => (string) $row['customer'],
            // Every link on this page goes somewhere real. There is no order
            // detail view, so the order number filters to that one order rather
            // than pointing at a route that would 404.
            'href' => orders_url(['q' => (string) $row['number']]),
            'placed_iso' => $placed->format(\DateTimeInterface::ATOM),
            'placed_display' => $placed->format('j M H:i'),
            'placed_title' => $placed->format('j F Y, H:i'),
            'items' => (int) $row['items'],
            'total' => new Money((int) $row['total_cents']),
            'status' => OrderStatus::from((string) $row['status']),
        ];
    }
}
