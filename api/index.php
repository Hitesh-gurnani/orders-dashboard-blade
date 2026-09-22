<?php

declare(strict_types=1);

/*
 * Deployment entry point, and nothing more.
 *
 * Vercel's PHP runtime looks for a serverless function under /api, so this file
 * exists to be that and immediately hands over. The application is public/index.php,
 * which is where the interesting wiring lives and where `composer serve` points.
 *
 * Nothing in the project depends on this file -- delete it and the local app is
 * unchanged.
 */

require __DIR__ . '/../public/index.php';
