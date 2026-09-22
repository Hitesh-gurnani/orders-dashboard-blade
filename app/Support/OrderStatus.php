<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The domain vocabulary for an order's fulfilment state.
 *
 * Lives in App\Support rather than App\View\Components deliberately: the
 * repository filters and counts by it long before anything renders it.
 */
enum OrderStatus: string
{
    case Fulfilled = 'fulfilled';
    case Processing = 'processing';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Cancelled orders are struck from the ledger: they contribute nothing to
     * revenue. Refunded ones DO count, negatively, because the money moved.
     */
    public function countsTowardRevenue(): bool
    {
        return $this !== self::Cancelled;
    }

    /**
     * Whether an order is one of the orders the average is taken OVER.
     *
     * This is a different question from countsTowardRevenue(), and conflating
     * the two is how you get an average that looks plausible and is wrong.
     * Cancelled orders never happened. Refunded orders DID happen and were then
     * reversed -- so they still pull revenue down, but they are not orders that
     * produced value, and including them in the denominator would understate
     * what a surviving order is worth.
     *
     * The consequence is visible on screen: net revenue divided by the average
     * gives 37, and the status chips say 29 fulfilled plus 8 processing.
     */
    public function countsTowardAverage(): bool
    {
        return $this === self::Fulfilled || $this === self::Processing;
    }

    /**
     * Whether a row in this state gets the left rail.
     *
     * Fulfilled is the majority state and is deliberately unmarked, so a
     * healthy queue has a blank left edge and the eye is drawn to exactly the
     * rows that need work. Marking every row marks nothing.
     *
     * This lives on the enum rather than on Badge because the <tr> is what
     * needs the answer, and the <tr> has the status, not the badge.
     */
    public function isException(): bool
    {
        return $this !== self::Fulfilled;
    }
}
