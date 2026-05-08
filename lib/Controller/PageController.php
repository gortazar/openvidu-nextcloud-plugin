<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Controller;

use OCA\OpenViduIntegration\AppInfo\Application;
use OCA\OpenViduIntegration\Service\RoomService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Renders the main dashboard and the per-room embed page.
 *
 * Both actions are marked @NoCSRFRequired because they are standard GET page
 * loads (the browser does not send a CSRF token for normal navigation).
 * They are marked @NoAdminRequired because every authenticated user may use
 * the video-conferencing feature.
 */
class PageController extends Controller {
	public function __construct(
		IRequest                   $request,
		private readonly IUserSession $userSession,
		private readonly RoomService  $roomService,
		private readonly IConfig      $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Renders the main room-list page.
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): TemplateResponse {
		$user   = $this->userSession->getUser();
		$userId = $user?->getUID() ?? '';
		$rooms  = $this->roomService->getRoomsForUser($userId);

		$openViduMeetUrl = $this->config->getAppValue(
			Application::APP_ID,
			'openvidu_meet_url',
			''
		);

		return new TemplateResponse(Application::APP_ID, 'main', [
			'rooms'          => array_map(fn($r) => $r->toArray(), $rooms),
			'openViduMeetUrl' => $openViduMeetUrl,
			'userId'         => $userId,
		]);
	}

	/**
	 * Renders the room-embed page for a given token.
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function room(string $token): TemplateResponse {
		$openViduMeetUrl = $this->config->getAppValue(
			Application::APP_ID,
			'openvidu_meet_url',
			''
		);
		$openViduApiKey = $this->config->getAppValue(
			Application::APP_ID,
			'openvidu_api_key',
			''
		);
		$room = $this->roomService->getRoomByToken($token);

		return new TemplateResponse(Application::APP_ID, 'room', [
			'token'           => $token,
			'room'            => $room?->toArray(),
			'openViduMeetUrl' => $openViduMeetUrl,
			'openViduApiKey'  => $openViduApiKey,
		]);
	}
}
