<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Settings;

use OCA\OpenViduIntegration\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

/**
 * Renders the admin-settings form embedded in the Nextcloud admin panel.
 *
 * Design note: this class only *renders* the form.  Saving is handled by
 * AdminController::saveSettings() which is called via AJAX from the template's
 * JavaScript.
 */
class Admin implements ISettings {
	public function __construct(
		private readonly IConfig $config,
	) {
	}

	public function getForm(): TemplateResponse {
		$openViduMeetUrl = $this->config->getAppValue(
			Application::APP_ID,
			'openvidu_meet_url',
			''
		);

		return new TemplateResponse(
			Application::APP_ID,
			'admin',
			['openvidu_meet_url' => $openViduMeetUrl],
			TemplateResponse::RENDER_AS_BLANK
		);
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 50;
	}
}
