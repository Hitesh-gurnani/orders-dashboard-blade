<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Integer cents, formatted once.
 *
 * This is a value object in App\Support, NOT a view component -- which is why
 * having it alongside the one class-backed component does not break the
 * one-class rule. It also must not live in App\View\Components: the component
 * tag compiler guesses a class name from <x-money>, and a real class sitting at
 * that path would be constructed as a component instead of falling through to
 * the anonymous template.
 */
final class Money
{
    /** Dollars with thousand separators, unsigned: '1,472' */
    public readonly string $int;

    /** Cents including the point, always two digits: '.20' */
    public readonly string $dec;

    public readonly bool $isNegative;

    public function __construct(public readonly int $cents)
    {
        $this->isNegative = $cents < 0;

        $absolute = abs($cents);
        $this->int = number_format(intdiv($absolute, 100));
        $this->dec = '.' . str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    public function plus(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    /** U+2212 MINUS SIGN, not a hyphen: it aligns with the digits. */
    public function sign(): string
    {
        return $this->isNegative ? "\u{2212}" : '';
    }
}
