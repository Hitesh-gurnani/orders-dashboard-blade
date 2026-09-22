<?php

declare(strict_types=1);

use App\Support\OrderStatus;
use App\View\Components\Badge;

/**
 * The README claims the status mapping lives in a class SO THAT it can be
 * tested. That claim is rhetoric until a test imports it. This file is what
 * makes it true, and it is the entire reason Badge is not an anonymous
 * component with a match expression in the template.
 */

echo "Badge\n";

it('maps each status to a variant, label and glyph', function (): void {
    $expected = [
        'fulfilled' => 'Fulfilled',
        'processing' => 'Processing',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ];

    foreach (OrderStatus::cases() as $status) {
        $badge = new Badge($status);

        same($status->value, $badge->variant, "variant for {$status->value}");
        same($expected[$status->value], $badge->label, "label for {$status->value}");

        if ($badge->glyphPath === '') {
            throw new RuntimeException("no glyph path for {$status->value}");
        }

        if ($badge->description === '') {
            throw new RuntimeException("no description for {$status->value}");
        }
    }
});

it('gives every status a distinct glyph, so shape carries the meaning', function (): void {
    $paths = array_map(
        static fn (OrderStatus $status): string => (new Badge($status))->glyphPath,
        OrderStatus::cases()
    );

    same(count($paths), count(array_unique($paths)), 'distinct glyph paths');
});

it('accepts the raw string a database would hand it', function (): void {
    same('refunded', (new Badge('refunded'))->variant);
    same('Refunded', (new Badge('refunded'))->label);
});

it('falls back to a neutral variant for an unrecognised status', function (): void {
    // Data from a system you do not control eventually contains a status you
    // did not plan for. A dashboard that throws on it is a dashboard you cannot
    // use on the day you most need it.
    $badge = new Badge('partially_shipped');

    same('unknown', $badge->variant);
    same('Partially shipped', $badge->label);
});

it('never announces the label twice', function (): void {
    // The description is spoken after the visible label, so it must not repeat
    // it -- "Refunded, Refunded, payment returned" is a real bug that only a
    // screen-reader user ever hears.
    foreach (OrderStatus::cases() as $status) {
        $badge = new Badge($status);

        if (stripos($badge->description, $badge->label) !== false) {
            throw new RuntimeException("description repeats the label for {$status->value}");
        }
    }
});

it('marks every status except fulfilled as an exception', function (): void {
    // The sparse rail depends on this: if fulfilled ever gained a rail, a
    // healthy page would stripe from top to bottom and the device would stop
    // meaning anything.
    same(false, OrderStatus::Fulfilled->isException());
    same(true, OrderStatus::Processing->isException());
    same(true, OrderStatus::Refunded->isException());
    same(true, OrderStatus::Cancelled->isException());
});
