<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\OrderStatus;
use Illuminate\View\Component;

/**
 * The only class-backed component in this project.
 *
 * WHY THIS ONE AND NOTHING ELSE
 * One input -- a fulfilment status -- produces four derived values: a variant
 * slug, a human label, an SVG glyph, and a spoken gloss for screen readers.
 * Expressed in a template that is a match expression plus a ternary chain plus
 * a lookup array, sitting in the view layer, repeated anywhere else that needs
 * one of the four. Expressed as a class it is one source of truth with a test
 * around it, and the payoff is visible one file away: table/row.blade.php says
 *
 *     <x-badge :status="$order['status']" />
 *
 * and badge.blade.php has no conditional in it at all.
 *
 * Every other component in this project is an anonymous .blade.php file,
 * because none of them derive anything -- they arrange what they are handed.
 * That is the whole distinction, and it is the reason this class exists.
 *
 * WHY THE CONSTRUCTOR INJECTS NOTHING
 * Component::resolve() reflects the constructor and, when every parameter name
 * is present in the data passed to the tag, calls `new static(...)` directly --
 * the container is never touched on the render path. Injecting a dependency
 * here would trade that for a container resolution on every one of twenty rows,
 * to save nothing. One parameter, no injection, deliberately.
 */
final class Badge extends Component
{
    /** fulfilled | processing | refunded | cancelled | unknown */
    public readonly string $variant;

    public readonly string $label;

    /** An SVG path 'd' value drawn on a fixed 10x10 viewBox, fill-rule evenodd. */
    public readonly string $glyphPath;

    /**
     * Spoken after the visible label, so the badge announces as
     * "Refunded, payment returned to the customer" rather than a bare word.
     * It does not repeat the label -- that would announce it twice.
     */
    public readonly string $description;

    public function __construct(public readonly OrderStatus|string $status)
    {
        $resolved = $status instanceof OrderStatus
            ? $status
            : OrderStatus::tryFrom($status);

        // An unrecognised status renders neutrally with its raw value title-cased,
        // rather than throwing or -- worse -- rendering an empty chip. Data from
        // a system you do not control eventually contains a status you did not
        // plan for, and a dashboard that dies on it is a dashboard you cannot use.
        [$this->variant, $this->label, $this->glyphPath, $this->description] = match ($resolved) {
            OrderStatus::Fulfilled => [
                'fulfilled',
                'Fulfilled',
                'M1 1h8v8H1Z',
                'shipped and paid',
            ],
            OrderStatus::Processing => [
                'processing',
                'Processing',
                'M1 1h8v8H1ZM2 2v6h6V2ZM2 2h3v6H2Z',
                'not yet fulfilled',
            ],
            OrderStatus::Refunded => [
                'refunded',
                'Refunded',
                'M1 1h8v8H1ZM2 2v6h6V2ZM2 4.25h6v1.5H2Z',
                'payment returned to the customer',
            ],
            OrderStatus::Cancelled => [
                'cancelled',
                'Cancelled',
                'M1 1h8v8H1ZM2 2v6h6V2ZM2.75 6.9 6.9 2.75l.6.6L3.35 7.5Z',
                'struck from the ledger and excluded from revenue',
            ],
            null => [
                'unknown',
                ucfirst(str_replace(['_', '-'], ' ', (string) $status)),
                'M1 1h8v8H1ZM2 2v6h6V2Z',
                'unrecognised status',
            ],
        };
    }

    /**
     * Returns a view NAME, never a raw Blade string. Component::render() given
     * markup instead of a name routes through createBladeViewFromString(), which
     * reaches into the container for 'config' -- a service this project
     * deliberately does not have. Returning a name keeps the dependency list
     * honest. See README, "Running Blade without Laravel".
     */
    public function render(): string
    {
        return 'components.badge';
    }
}
