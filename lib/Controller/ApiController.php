<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Controller;

use OCA\OpenViduIntegration\AppInfo\Application;
use OCA\OpenViduIntegration\Db\Room;
use OCA\OpenViduIntegration\Service\RoomNotFoundException;
use OCA\OpenViduIntegration\Service\RoomService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * JSON REST API for room management.
 *
 * All endpoints require a valid user session (@NoAdminRequired = any user).
 * CSRF protection is handled by Nextcloud's built-in RequestToken header check
 * (the JS frontend sends the token automatically).
 */
class ApiController extends Controller {
	public function __construct(
		IRequest                   $request,
		private readonly IUserSession $userSession,
		private readonly RoomService  $roomService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * GET /api/rooms
	 * Returns all rooms for the currently authenticated user.
	 */
	#[NoAdminRequired]
	public function getRooms(): DataResponse {
		$userId = $this->getCurrentUserId();
		$rooms  = $this->roomService->getRoomsForUser($userId);

		return new DataResponse(array_map(fn(Room $r) => $r->toArray(), $rooms));
	}

	/**
	 * POST /api/rooms
	 * Creates a new room with the given name.
	 *
	 * Expected body: { "name": "My Room" }
	 */
	#[NoAdminRequired]
	public function createRoom(string $name = ''): DataResponse {
		if (trim($name) === '') {
			return new DataResponse(
				['error' => 'Room name cannot be empty'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$userId = $this->getCurrentUserId();
			$room   = $this->roomService->createRoom($name, $userId);
			return new DataResponse($room->toArray(), Http::STATUS_CREATED);
		} catch (\InvalidArgumentException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * DELETE /api/rooms/{token}
	 * Deletes the room identified by $token; only the owner may do so.
	 */
	#[NoAdminRequired]
	public function deleteRoom(string $token): DataResponse {
		try {
			$userId = $this->getCurrentUserId();
			$this->roomService->deleteRoom($token, $userId);
			return new DataResponse([]);
		} catch (RoomNotFoundException $e) {
			return new DataResponse(['error' => 'Room not found'], Http::STATUS_NOT_FOUND);
		} catch (\RuntimeException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
		}
	}

	// ---------------------------------------------------------------------------
	// Helpers
	// ---------------------------------------------------------------------------

	private function getCurrentUserId(): string {
		return $this->userSession->getUser()?->getUID() ?? '';
	}
}
