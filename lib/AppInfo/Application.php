<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'openviduintegration';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// All services are registered automatically via DI container reflection.
		// Explicit registrations (e.g. aliases, event listeners) would go here.
	}

	public function boot(IBootContext $context): void {
	}
}
