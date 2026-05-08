<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap for OpenVidu Integration unit tests.
 *
 * The tests run in isolation (no live Nextcloud server is required).  All OCP
 * interfaces are mocked with PHPUnit's createMock() helper or provided by the
 * local OCP stubs file.
 *
 * Load order:
 *  1. Composer autoloader (registers OCA\OpenViduIntegration\… namespaces)
 *  2. OCP stubs (registers OCP\… namespaces so production code can be loaded
 *     without an actual Nextcloud installation)
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Only load stubs if the real OCP package is not available.
if (!interface_exists(\OCP\IRequest::class, false)) {
    require_once __DIR__ . '/Stubs/OcpStubs.php';
}
