<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Tests\Unit\Service;

use OCA\OpenViduIntegration\Db\Room;
use OCA\OpenViduIntegration\Db\RoomMapper;
use OCA\OpenViduIntegration\Service\RoomNotFoundException;
use OCA\OpenViduIntegration\Service\RoomService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for RoomService.
 *
 * All dependencies (RoomMapper, ISecureRandom) are mocked so that the tests
 * exercise only the business logic without touching a database.
 */
class RoomServiceTest extends TestCase {
	private RoomService $service;

	/** @var RoomMapper&MockObject */
	private RoomMapper $mapper;

	/** @var ISecureRandom&MockObject */
	private ISecureRandom $random;

	protected function setUp(): void {
		$this->mapper  = $this->createMock(RoomMapper::class);
		$this->random  = $this->createMock(ISecureRandom::class);
		$this->service = new RoomService($this->mapper, $this->random);
	}

	// -----------------------------------------------------------------------
	// getRoomsForUser
	// -----------------------------------------------------------------------

	public function testGetRoomsForUserDelegatesToMapper(): void {
		$room = $this->makeRoom('alice', 'tok1');

		$this->mapper->expects($this->once())
			->method('findAllByUser')
			->with('alice')
			->willReturn([$room]);

		$result = $this->service->getRoomsForUser('alice');

		$this->assertCount(1, $result);
		$this->assertSame($room, $result[0]);
	}

	public function testGetRoomsForUserReturnsEmptyArrayWhenNoRooms(): void {
		$this->mapper->method('findAllByUser')->willReturn([]);

		$result = $this->service->getRoomsForUser('nobody');
		$this->assertSame([], $result);
	}

	// -----------------------------------------------------------------------
	// getRoomByToken
	// -----------------------------------------------------------------------

	public function testGetRoomByTokenReturnsRoomOnSuccess(): void {
		$room = $this->makeRoom('alice', 'tok1');
		$this->mapper->method('findByToken')->with('tok1')->willReturn($room);

		$result = $this->service->getRoomByToken('tok1');
		$this->assertSame($room, $result);
	}

	public function testGetRoomByTokenReturnsNullWhenNotFound(): void {
		$this->mapper->method('findByToken')
			->willThrowException(new DoesNotExistException('not found'));

		$result = $this->service->getRoomByToken('missing');
		$this->assertNull($result);
	}

	// -----------------------------------------------------------------------
	// createRoom
	// -----------------------------------------------------------------------

	public function testCreateRoomPersistsAndReturnsRoom(): void {
		$this->random->method('generate')->willReturn('abcd1234efgh5678');

		$inserted = $this->makeRoom('bob', 'abcd1234efgh5678', 'My Room');
		$this->mapper->expects($this->once())
			->method('insert')
			->willReturn($inserted);

		$result = $this->service->createRoom('My Room', 'bob');

		$this->assertSame($inserted, $result);
	}

	public function testCreateRoomTrimsWhitespaceFromName(): void {
		$this->random->method('generate')->willReturn('token000000000000');

		$captured = null;
		$this->mapper->method('insert')
			->willReturnCallback(function (Room $r) use (&$captured) {
				$captured = $r;
				return $r;
			});

		$this->service->createRoom('  Padded Name  ', 'alice');

		$this->assertSame('Padded Name', $captured->getName());
	}

	public function testCreateRoomThrowsOnEmptyName(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->service->createRoom('   ', 'alice');
	}

	public function testCreateRoomSetsUserId(): void {
		$this->random->method('generate')->willReturn('token000000000000');

		$captured = null;
		$this->mapper->method('insert')
			->willReturnCallback(function (Room $r) use (&$captured) {
				$captured = $r;
				return $r;
			});

		$this->service->createRoom('Room', 'charlie');
		$this->assertSame('charlie', $captured->getUserId());
	}

	public function testCreateRoomSetsGeneratedToken(): void {
		$this->random->method('generate')->willReturn('generated1234567');

		$captured = null;
		$this->mapper->method('insert')
			->willReturnCallback(function (Room $r) use (&$captured) {
				$captured = $r;
				return $r;
			});

		$this->service->createRoom('Room', 'dave');
		$this->assertSame('generated1234567', $captured->getToken());
	}

	public function testCreateRoomSetsCreatedAt(): void {
		$this->random->method('generate')->willReturn('tok');

		$captured = null;
		$this->mapper->method('insert')
			->willReturnCallback(function (Room $r) use (&$captured) {
				$captured = $r;
				return $r;
			});

		$before = new \DateTime('now', new \DateTimeZone('UTC'));
		$this->service->createRoom('Room', 'eve');
		$after = new \DateTime('now', new \DateTimeZone('UTC'));

		$created = new \DateTime($captured->getCreatedAt(), new \DateTimeZone('UTC'));
		$this->assertGreaterThanOrEqual($before->getTimestamp(), $created->getTimestamp());
		$this->assertLessThanOrEqual($after->getTimestamp(), $created->getTimestamp());
	}

	// -----------------------------------------------------------------------
	// deleteRoom
	// -----------------------------------------------------------------------

	public function testDeleteRoomDeletesWhenOwnerMatches(): void {
		$room = $this->makeRoom('frank', 'tok42');
		$this->mapper->method('findByToken')->with('tok42')->willReturn($room);
		$this->mapper->expects($this->once())->method('delete')->with($room);

		$this->service->deleteRoom('tok42', 'frank');
	}

	public function testDeleteRoomThrowsRoomNotFoundWhenTokenMissing(): void {
		$this->mapper->method('findByToken')
			->willThrowException(new DoesNotExistException('gone'));

		$this->expectException(RoomNotFoundException::class);
		$this->service->deleteRoom('notoken', 'someone');
	}

	public function testDeleteRoomThrowsRuntimeExceptionWhenNotOwner(): void {
		$room = $this->makeRoom('owner', 'tok99');
		$this->mapper->method('findByToken')->willReturn($room);
		$this->mapper->expects($this->never())->method('delete');

		$this->expectException(\RuntimeException::class);
		$this->service->deleteRoom('tok99', 'attacker');
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	private function makeRoom(string $userId, string $token, string $name = 'Room'): Room {
		$room = new Room();
		$room->setUserId($userId);
		$room->setToken($token);
		$room->setName($name);
		return $room;
	}
}
