<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Initial schema: creates the openviduintegration_rooms table.
 */
class Version0001Date20240101000000 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('openviduintegration_rooms')) {
			return null;
		}

		$table = $schema->createTable('openviduintegration_rooms');

		$table->addColumn('id', Types::INTEGER, [
			'autoincrement' => true,
			'notnull'       => true,
		]);
		$table->addColumn('name', Types::STRING, [
			'notnull' => true,
			'length'  => 255,
		]);
		// Short random token used as a public room identifier in URLs
		$table->addColumn('token', Types::STRING, [
			'notnull' => true,
			'length'  => 64,
		]);
		$table->addColumn('user_id', Types::STRING, [
			'notnull' => true,
			'length'  => 64,
		]);
		$table->addColumn('created_at', Types::STRING, [
			'notnull' => true,
			'length'  => 32,
		]);

		$table->setPrimaryKey(['id']);
		$table->addUniqueIndex(['token'],   'openvidu_room_token_idx');
		$table->addIndex(['user_id'],        'openvidu_room_user_idx');

		return $schema;
	}
}
