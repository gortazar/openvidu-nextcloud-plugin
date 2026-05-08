<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Tests\Unit\Controller;

use OCA\OpenViduIntegration\Controller\ApiController;
use OCA\OpenViduIntegration\Db\Room;
use OCA\OpenViduIntegration\Service\RoomNotFoundException;
use OCA\OpenViduIntegration\Service\RoomService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ApiController.
 */
class ApiControllerTest extends TestCase {
	private ApiController $controller;

	/** @var IUserSession&MockObject */
	private IUserSession $session;

	/** @var RoomService&MockObject */
	private RoomService $roomService;

	protected function setUp(): void {
		$request           = $this->createMock(IRequest::class);
		$this->session     = $this->createMock(IUserSession::class);
		$this->roomService = $this->createMock(RoomService::class);

		$this->controller = new ApiController(
			$request,
			$this->session,
			$this->roomService,
		);
	}

	// -----------------------------------------------------------------------
	// getRooms()
	// -----------------------------------------------------------------------

	public function testGetRoomsReturnsEmptyArrayForNewUser(): void {
		$this->mockUser('alice');
		$this->roomService->method('getRoomsForUser')->willReturn([]);

		$response = $this->controller->getRooms();

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame([], $response->getData());
	}

	public function testGetRoomsReturnsSerializedRooms(): void {
		$this->mockUser('alice');

		$room = $this->makeRoom('alice', 'tok1', 'My Room');
		$this->roomService->method('getRoomsForUser')
			->with('alice')
			->willReturn([$room]);

		$data = $this->controller->getRooms()->getData();

		$this->assertCount(1, $data);
		$this->assertSame('tok1',    $data[0]['token']);
		$this->assertSame('My Room', $data[0]['name']);
	}

	// -----------------------------------------------------------------------
	// createRoom()
	// -----------------------------------------------------------------------

	public function testCreateRoomReturns201OnSuccess(): void {
		$this->mockUser('alice');
		$room = $this->makeRoom('alice', 'newtok', 'New Room');
		$this->roomService->method('createRoom')
			->with('New Room', 'alice')
			->willReturn($room);

		$response = $this->controller->createRoom('New Room');

		$this->assertSame(Http::STATUS_CREATED, $response->getStatus());
	}

	public function testCreateRoomReturnsRoomData(): void {
		$this->mockUser('bob');
		$room = $this->makeRoom('bob', 't0k', 'Demo');
		$this->roomService->method('createRoom')->willReturn($room);

		$data = $this->controller->createRoom('Demo')->getData();

		$this->assertSame('t0k',  $data['token']);
		$this->assertSame('Demo', $data['name']);
	}

	public function testCreateRoomReturns400ForEmptyName(): void {
		$this->mockUser('alice');
		$this->roomService->expects($this->never())->method('createRoom');

		$response = $this->controller->createRoom('   ');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testCreateRoomReturns400ForMissingName(): void {
		$this->mockUser('alice');
		$this->roomService->expects($this->never())->method('createRoom');

		$response = $this->controller->createRoom('');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testCreateRoomReturns400WhenServiceThrowsInvalidArgument(): void {
		$this->mockUser('alice');
		$this->roomService->method('createRoom')
			->willThrowException(new \InvalidArgumentException('Name too long'));

		$response = $this->controller->createRoom('Valid looking name');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	// -----------------------------------------------------------------------
	// deleteRoom()
	// -----------------------------------------------------------------------

	public function testDeleteRoomReturns200OnSuccess(): void {
		$this->mockUser('alice');
		$this->roomService->method('deleteRoom');

		$response = $this->controller->deleteRoom('tok1');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function testDeleteRoomReturns404WhenNotFound(): void {
		$this->mockUser('alice');
		$this->roomService->method('deleteRoom')
			->willThrowException(new RoomNotFoundException());

		$response = $this->controller->deleteRoom('missing');

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}

	public function testDeleteRoomReturns403WhenNotOwner(): void {
		$this->mockUser('attacker');
		$this->roomService->method('deleteRoom')
			->willThrowException(new \RuntimeException('Not authorized to delete this room'));

		$response = $this->controller->deleteRoom('tok1');

		$this->assertSame(Http::STATUS_FORBIDDEN, $response->getStatus());
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	private function mockUser(string $uid): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$this->session->method('getUser')->willReturn($user);
	}

	private function makeRoom(string $userId, string $token, string $name = 'Room'): Room {
		$room = new Room();
		$room->setId(1);
		$room->setUserId($userId);
		$room->setToken($token);
		$room->setName($name);
		return $room;
	}
}
