<?php

declare(strict_types=1);

/**
 * A test runner with no dependencies, because a sample this size does not earn
 * a test framework -- and reaching for one anyway is its own kind of signal.
 *
 * Run with: composer test
 */

require __DIR__ . '/../vendor/autoload.php';

$passed = 0;
$failed = 0;

function it(string $description, callable $assertion): void
{
    global $passed, $failed;

    try {
        $assertion();
        $passed++;
        echo "  ok    {$description}\n";
    } catch (Throwable $e) {
        $failed++;
        echo "  FAIL  {$description}\n        {$e->getMessage()}\n";
    }
}

function same(mixed $expected, mixed $actual, string $what = 'value'): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s: expected %s, got %s',
            $what,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

require __DIR__ . '/badge_test.php';
require __DIR__ . '/repository_test.php';

echo "\n{$passed} passed, {$failed} failed\n";

exit($failed === 0 ? 0 : 1);
