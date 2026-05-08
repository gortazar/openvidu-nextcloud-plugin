<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Tests\Unit\Db;

use OCA\OpenViduIntegration\Db\Room;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Room entity's toArray() serialization and setter/getter behaviour.
 */
class RoomTest extends TestCase {
	private Room $room;

	protected function setUp(): void {
		$this->room = new Room();
	}

	public function testDefaultValuesAreEmpty(): void {
		// All string properties should default to the empty string.
		$this->assertSame('', $this->room->getName());
		$this->assertSame('', $this->room->getToken());
		$this->assertSame('', $this->room->getUserId());
		$this->assertSame('', $this->room->getCreatedAt());
	}

	public function testSettersAndGetters(): void {
		$this->room->setName('My Room');
		$this->room->setToken('abc123');
		$this->room->setUserId('alice');
		$this->room->setCreatedAt('2024-01-01 10:00:00');

		$this->assertSame('My Room',             $this->room->getName());
		$this->assertSame('abc123',              $this->room->getToken());
		$this->assertSame('alice',               $this->room->getUserId());
		$this->assertSame('2024-01-01 10:00:00', $this->room->getCreatedAt());
	}

	public function testToArrayContainsAllExpectedKeys(): void {
		$this->room->setId(42);
		$this->room->setName('Test');
		$this->room->setToken('tok');
		$this->room->setUserId('bob');
		$this->room->setCreatedAt('2024-06-01 08:00:00');

		$arr = $this->room->toArray();

		$this->assertArrayHasKey('id',        $arr);
		$this->assertArrayHasKey('name',      $arr);
		$this->assertArrayHasKey('token',     $arr);
		$this->assertArrayHasKey('userId',    $arr);
		$this->assertArrayHasKey('createdAt', $arr);
	}

	public function testToArrayValuesMatchSetters(): void {
		$this->room->setId(7);
		$this->room->setName('Demo Room');
		$this->room->setToken('xyz789');
		$this->room->setUserId('carol');
		$this->room->setCreatedAt('2024-12-31 23:59:59');

		$arr = $this->room->toArray();

		$this->assertSame(7,                     $arr['id']);
		$this->assertSame('Demo Room',           $arr['name']);
		$this->assertSame('xyz789',              $arr['token']);
		$this->assertSame('carol',               $arr['userId']);
		$this->assertSame('2024-12-31 23:59:59', $arr['createdAt']);
	}
}
