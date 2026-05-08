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
	 * Expected body:
	 *   { "openvidu_meet_url": "https://…", "openvidu_api_key": "…" }
	 *
	 * The API key is sent as the X-API-KEY header on every request to the
	 * OpenVidu Meet server (OpenVidu v3 requirement).
	 */
	public function saveSettings(
		string $openvidu_meet_url = '',
		string $openvidu_api_key  = '',
	): DataResponse {
		$url    = trim($openvidu_meet_url);
		$apiKey = trim($openvidu_api_key);

		if ($url !== '') {
			// Must be a syntactically valid URL …
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				return new DataResponse(
					['error' => 'Invalid URL'],
					\OCP\AppFramework\Http::STATUS_BAD_REQUEST
				);
			}
			// … and restricted to http / https to prevent javascript:, ftp:, etc.
			$scheme = parse_url($url, PHP_URL_SCHEME);
			if (!in_array($scheme, ['http', 'https'], true)) {
				return new DataResponse(
					['error' => 'URL must use http or https scheme'],
					\OCP\AppFramework\Http::STATUS_BAD_REQUEST
				);
			}
		}

		// Strip trailing slash so that token concatenation is always clean.
		$url = rtrim($url, '/');

		$this->config->setAppValue(Application::APP_ID, 'openvidu_meet_url', $url);
		$this->config->setAppValue(Application::APP_ID, 'openvidu_api_key', $apiKey);

		return new DataResponse([
			'openvidu_meet_url' => $url,
			'openvidu_api_key'  => $apiKey,
		]);
	}
}
