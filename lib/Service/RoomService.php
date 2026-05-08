<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Service;

use OCA\OpenViduIntegration\Db\Room;
use OCA\OpenViduIntegration\Db\RoomMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Security\ISecureRandom;

/**
 * Business-logic layer for meeting-room management.
 *
 * Design decisions:
 *  – Token generation uses ISecureRandom (Nextcloud's CSPRNG wrapper) so that
 *    room tokens are cryptographically unpredictable.
 *  – The service owns validation: empty names are rejected here rather than in
 *    the controller so that CLI commands / cron jobs enjoy the same protection.
 *  – Only the room creator is allowed to delete their own room; the check lives
 *    here (not in the controller) to keep authorization logic in one place.
 */
class RoomService {
	/** Length (chars) of the random room token. */
	private const TOKEN_LENGTH = 16;

	public function __construct(
		private readonly RoomMapper  $roomMapper,
		private readonly ISecureRandom $random,
	) {
	}

	/**
	 * Returns all rooms owned by $userId, ordered newest-first.
	 *
	 * @return Room[]
	 */
	public function getRoomsForUser(string $userId): array {
		return $this->roomMapper->findAllByUser($userId);
	}

	/**
	 * Returns the room with the given token, or null if it does not exist.
	 */
	public function getRoomByToken(string $token): ?Room {
		try {
			return $this->roomMapper->findByToken($token);
		} catch (DoesNotExistException) {
			return null;
		}
	}

	/**
	 * Creates and persists a new room.
	 *
	 * @throws \InvalidArgumentException when $name is empty after trimming
	 */
	public function createRoom(string $name, string $userId): Room {
		$name = trim($name);
		if ($name === '') {
			throw new \InvalidArgumentException('Room name cannot be empty');
		}

		$token = $this->random->generate(
			self::TOKEN_LENGTH,
			ISecureRandom::CHAR_LOWER . ISecureRandom::CHAR_DIGITS
		);

		$room = new Room();
		$room->setName($name);
		$room->setToken($token);
		$room->setUserId($userId);
		$room->setCreatedAt((new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'));

		return $this->roomMapper->insert($room);
	}

	/**
	 * Deletes the room identified by $token.
	 *
	 * @throws RoomNotFoundException   when the token does not exist
	 * @throws \RuntimeException       when $userId is not the room owner
	 */
	public function deleteRoom(string $token, string $userId): void {
		try {
			$room = $this->roomMapper->findByToken($token);
		} catch (DoesNotExistException) {
			throw new RoomNotFoundException();
		}

		if ($room->getUserId() !== $userId) {
			throw new \RuntimeException('Not authorized to delete this room');
		}

		$this->roomMapper->delete($room);
	}
}
