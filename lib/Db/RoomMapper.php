<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Room>
 */
class RoomMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'openviduintegration_rooms', Room::class);
	}

	/**
	 * Returns all rooms created by the given user, newest first.
	 *
	 * @return Room[]
	 */
	public function findAllByUser(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq(
				'user_id',
				$qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)
			))
			->orderBy('created_at', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * Find a room by its unique token.
	 *
	 * @throws DoesNotExistException when no room with that token exists
	 */
	public function findByToken(string $token): Room {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq(
				'token',
				$qb->createNamedParameter($token, IQueryBuilder::PARAM_STR)
			));

		return $this->findEntity($qb);
	}
}
