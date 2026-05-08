<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Tests\Unit\Controller;

use OCA\OpenViduIntegration\Controller\PageController;
use OCA\OpenViduIntegration\Db\Room;
use OCA\OpenViduIntegration\Service\RoomService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PageController.
 */
class PageControllerTest extends TestCase {
	private PageController $controller;

	/** @var IUserSession&MockObject */
	private IUserSession $session;

	/** @var RoomService&MockObject */
	private RoomService $roomService;

	/** @var IConfig&MockObject */
	private IConfig $config;

	protected function setUp(): void {
		$request           = $this->createMock(IRequest::class);
		$this->session     = $this->createMock(IUserSession::class);
		$this->roomService = $this->createMock(RoomService::class);
		$this->config      = $this->createMock(IConfig::class);

		$this->controller = new PageController(
			$request,
			$this->session,
			$this->roomService,
			$this->config,
		);
	}

	// -----------------------------------------------------------------------
	// index()
	// -----------------------------------------------------------------------

	public function testIndexReturnsTemplateResponse(): void {
		$this->mockUser('alice');
		$this->roomService->method('getRoomsForUser')->willReturn([]);
		$this->config->method('getAppValue')->willReturn('');

		$response = $this->controller->index();

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertSame('main', $response->getTemplateName());
	}

	public function testIndexPassesRoomsToTemplate(): void {
		$this->mockUser('alice');

		$room = new Room();
		$room->setId(1);
		$room->setName('My Meeting');
		$room->setToken('abc123');
		$room->setUserId('alice');

		$this->roomService->method('getRoomsForUser')
			->with('alice')
			->willReturn([$room]);

		$this->config->method('getAppValue')->willReturn('https://meet.example.com');

		$params = $this->controller->index()->getParams();

		$this->assertCount(1, $params['rooms']);
		$this->assertSame('abc123', $params['rooms'][0]['token']);
	}

	public function testIndexPassesOpenViduMeetUrlToTemplate(): void {
		$this->mockUser('alice');
		$this->roomService->method('getRoomsForUser')->willReturn([]);
		$this->config->method('getAppValue')->willReturn('https://meet.example.com');

		$params = $this->controller->index()->getParams();

		$this->assertSame('https://meet.example.com', $params['openViduMeetUrl']);
	}

	public function testIndexPassesUserIdToTemplate(): void {
		$this->mockUser('bob');
		$this->roomService->method('getRoomsForUser')->willReturn([]);
		$this->config->method('getAppValue')->willReturn('');

		$params = $this->controller->index()->getParams();

		$this->assertSame('bob', $params['userId']);
	}

	public function testIndexHandlesGuestUser(): void {
		// No user logged in (getUser() returns null)
		$this->session->method('getUser')->willReturn(null);
		$this->roomService->method('getRoomsForUser')->with('')->willReturn([]);
		$this->config->method('getAppValue')->willReturn('');

		$params = $this->controller->index()->getParams();

		$this->assertSame('', $params['userId']);
	}

	// -----------------------------------------------------------------------
	// room()
	// -----------------------------------------------------------------------

	public function testRoomReturnsTemplateResponse(): void {
		$room = $this->makeRoom('tok1', 'alice');
		$this->roomService->method('getRoomByToken')->with('tok1')->willReturn($room);
		$this->config->method('getAppValue')->willReturn('https://meet.example.com');

		$response = $this->controller->room('tok1');

		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertSame('room', $response->getTemplateName());
	}

	public function testRoomPassesTokenToTemplate(): void {
		$this->roomService->method('getRoomByToken')->willReturn(null);
		$this->config->method('getAppValue')->willReturn('');

		$params = $this->controller->room('xyz')->getParams();

		$this->assertSame('xyz', $params['token']);
	}

	public function testRoomPassesNullRoomToTemplateWhenNotFound(): void {
		$this->roomService->method('getRoomByToken')->willReturn(null);
		$this->config->method('getAppValue')->willReturn('https://meet.example.com');

		$params = $this->controller->room('missing')->getParams();

		$this->assertNull($params['room']);
	}

	public function testRoomPassesRoomArrayToTemplate(): void {
		$room = $this->makeRoom('tok1', 'alice');
		$this->roomService->method('getRoomByToken')->willReturn($room);
		$this->config->method('getAppValue')->willReturn('https://meet.example.com');

		$params = $this->controller->room('tok1')->getParams();

		$this->assertIsArray($params['room']);
		$this->assertSame('tok1', $params['room']['token']);
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	private function mockUser(string $uid): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$this->session->method('getUser')->willReturn($user);
	}

	private function makeRoom(string $token, string $userId): Room {
		$room = new Room();
		$room->setId(1);
		$room->setToken($token);
		$room->setUserId($userId);
		$room->setName('A Room');
		return $room;
	}
}
