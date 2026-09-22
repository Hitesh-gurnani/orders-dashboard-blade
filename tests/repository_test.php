<?php

declare(strict_types=1);

use App\Data\OrderRepository;

/**
 * These assertions exist to pin the three metric DEFINITIONS, not the numbers.
 * If someone later decides cancelled orders should count toward revenue, this
 * file is where that argument has to be had.
 */

echo "\nOrderRepository\n";

$repository = new OrderRepository;
$all = require __DIR__ . '/../app/Data/orders.php';

it('nets revenue over every order except cancelled', function () use ($repository, $all): void {
    // Fulfilled + processing at face value, refunded negative (the money moved),
    // cancelled struck out entirely. Sum the Total column on screen and this is
    // the number you get.
    same(14587728, $repository->netTotal($all)->cents);
});

it('counts all 44 orders regardless of status', function () use ($repository): void {
    $metrics = $repository->metrics();

    same('44', $metrics['orders']['value']);
    same('$145,877.28', $metrics['revenue']['value']);
});

it('averages over fulfilled and processing only', function () use ($repository): void {
    // 37, not 44 and not 41. Each wrong denominator produces a plausible-looking
    // number -- $3,315.39 and $3,557.98 -- which is exactly why the definition is
    // pinned here rather than left to whoever edits metrics() next.
    $metrics = $repository->metrics();

    same('$3,942.63', $metrics['aov']['value']);

    // The relationship a reader can check on screen: net revenue over the
    // average is the fulfilled + processing chip counts added together.
    $counts = $repository->statusCounts();
    same(37, $counts['fulfilled'] + $counts['processing']);
});

it('computes its deltas rather than printing typed ones', function () use ($repository): void {
    $metrics = $repository->metrics();

    same('6.4%', $metrics['revenue']['delta']);
    same('up', $metrics['revenue']['direction']);
    same('4.3%', $metrics['orders']['delta']);
    same('down', $metrics['orders']['direction']);
});

it('counts each status, and they sum to the total', function () use ($repository): void {
    $counts = $repository->statusCounts();

    same(44, $counts['all']);
    same(29, $counts['fulfilled']);
    same(8, $counts['processing']);
    same(4, $counts['refunded']);
    same(3, $counts['cancelled']);
    same($counts['all'], $counts['fulfilled'] + $counts['processing'] + $counts['refunded'] + $counts['cancelled']);
});

it('narrows by status', function () use ($repository): void {
    same(4, $repository->page('refunded', '', 'placed', 'desc', 1)['total']);
});

it('searches customer name and order number, case-insensitively', function () use ($repository): void {
    same(1, $repository->page('all', 'KEPLER', 'placed', 'desc', 1)['total']);
    same(1, $repository->page('all', '10-4778', 'placed', 'desc', 1)['total']);
});

it('returns nothing for the empty-state URL in the README', function () use ($repository): void {
    // /?status=cancelled&q=kepler -- Kepler Dynamics AB is fulfilled, so the
    // combination is genuinely empty. If this ever starts matching, the README
    // is telling the reader to click through to a page that is not empty.
    $result = $repository->page('cancelled', 'kepler', 'placed', 'desc', 1);

    same(0, $result['total']);
    same([], $result['rows']);
    same(0, $result['from']);
});

it('clamps the page number to the pages that exist', function () use ($repository): void {
    same(1, $repository->page('all', '', 'placed', 'desc', 0)['page']);
    same(3, $repository->page('all', '', 'placed', 'desc', 999)['page']);
    same(3, $repository->page('all', '', 'placed', 'desc', 1)['pages']);
});

it('sorts both directions', function () use ($repository): void {
    $descending = $repository->page('all', '', 'total', 'desc', 1)['rows'];
    $ascending = $repository->page('all', '', 'total', 'asc', 1)['rows'];

    same('#10-4799', $descending[0]['number']);  // $16,900.00, the largest
    same('#10-4818', $ascending[0]['number']);   // -$1,472.20, the most negative
});

it('keeps the footer total honest under a filter', function () use ($repository): void {
    // The footer sums the SAME definition as the Revenue card, which is why it
    // is labelled "net of cancelled" -- and why filtering to cancelled shows
    // three orders totalling nothing, rather than $1,000.15 the card excludes.
    $cancelled = $repository->page('cancelled', '', 'placed', 'desc', 1);

    same(3, $cancelled['total']);
    same(0, $repository->netTotal($cancelled['matched'])->cents);
});
