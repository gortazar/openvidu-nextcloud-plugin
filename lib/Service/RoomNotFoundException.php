<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Service;

use Exception;

/** Thrown when a room cannot be found. */
class RoomNotFoundException extends Exception {
	public function __construct(string $message = 'Room not found') {
		parent::__construct($message);
	}
}
