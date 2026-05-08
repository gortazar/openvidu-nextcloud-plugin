<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Controller;

use OCA\OpenViduIntegration\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

/**
 * Handles admin-settings API calls.
 *
 * The actual settings form is rendered by OCA\OpenViduIntegration\Settings\Admin
 * (which integrates with the Nextcloud admin panel).  This controller provides
 * the AJAX endpoint that the form's JavaScript calls to persist changes.
 *
 * Only server administrators may call these endpoints (enforced by Nextcloud's
 * routing layer when no @NoAdminRequired annotation is present).
 */
class AdminController extends Controller {
	public function __construct(
		IRequest              $request,
		private readonly IConfig $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * POST /api/admin/settings
	 * Persists admin configuration.
	 *
	 * Expected body: { "openvidu_meet_url": "https://…" }
	 */
	public function saveSettings(string $openvidu_meet_url = ''): DataResponse {
		$url = trim($openvidu_meet_url);

		// Basic URL validation: must be empty (clearing the value is allowed)
		// or a valid http(s) URL.
		if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
			return new DataResponse(
				['error' => 'Invalid URL'],
				\OCP\AppFramework\Http::STATUS_BAD_REQUEST
			);
		}

		// Strip trailing slash so that token concatenation is always clean.
		$url = rtrim($url, '/');

		$this->config->setAppValue(Application::APP_ID, 'openvidu_meet_url', $url);

		return new DataResponse(['openvidu_meet_url' => $url]);
	}
}
