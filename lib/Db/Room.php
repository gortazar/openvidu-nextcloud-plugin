<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Represents a persistent meeting room.
 *
 * @method ?int   getId()
 * @method string getName()
 * @method void   setName(string $name)
 * @method string getToken()
 * @method void   setToken(string $token)
 * @method string getUserId()
 * @method void   setUserId(string $userId)
 * @method string getCreatedAt()
 * @method void   setCreatedAt(string $createdAt)
 */
class Room extends Entity {
	protected string $name = '';
	protected string $token = '';
	protected string $userId = '';
	protected string $createdAt = '';

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('name', 'string');
		$this->addType('token', 'string');
		$this->addType('userId', 'string');
		$this->addType('createdAt', 'string');
	}

	/** Serialize the entity for API responses. */
	public function toArray(): array {
		return [
			'id'        => $this->getId(),
			'name'      => $this->getName(),
			'token'     => $this->getToken(),
			'userId'    => $this->getUserId(),
			'createdAt' => $this->getCreatedAt(),
		];
	}
}
