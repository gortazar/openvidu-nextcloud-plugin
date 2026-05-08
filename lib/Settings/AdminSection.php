<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Settings;

use OCA\OpenViduIntegration\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/**
 * Registers the "OpenVidu Integration" section in the Nextcloud admin panel.
 */
class AdminSection implements IIconSection {
	public function __construct(
		private readonly IL10N         $l10n,
		private readonly IURLGenerator $urlGenerator,
	) {
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10n->t('OpenVidu Integration');
	}

	public function getPriority(): int {
		return 50;
	}
}
