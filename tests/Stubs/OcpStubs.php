<?php

declare(strict_types=1);

/**
 * Minimal stubs for Nextcloud OCP interfaces and classes used in unit tests.
 *
 * These are NOT meant to be complete implementations – they just satisfy the
 * type system and provide enough surface area for PHPUnit mocking to work.
 *
 * All stubs live in the exact namespace expected by the production code so
 * that autoloading does not attempt to download the real packages during CI.
 */

namespace OCP\AppFramework\Db {

	abstract class Entity {
		protected ?int $id = null;
		private array $_updatedFields = [];

		public function __construct() {
		}

		public function getId(): ?int {
			return $this->id;
		}

		public function setId(int $id): void {
			$this->id = $id;
		}

		protected function addType(string $field, string $type): void {
		}

		public function markFieldUpdated(string $attribute): void {
			$this->_updatedFields[$attribute] = true;
		}

		public function getUpdatedFields(): array {
			return $this->_updatedFields;
		}

		public function __call(string $methodName, array $args): mixed {
			$prefix = substr($methodName, 0, 3);
			$fieldName = lcfirst(substr($methodName, 3));

			if ($prefix === 'set') {
				$this->$fieldName = $args[0];
				$this->markFieldUpdated($fieldName);
				return null;
			}

			if ($prefix === 'get') {
				return $this->$fieldName ?? null;
			}

			throw new \BadMethodCallException('Unknown method: ' . $methodName);
		}
	}

	class DoesNotExistException extends \Exception {
	}

	/**
	 * @template T of Entity
	 */
	abstract class QBMapper {
		protected object $db;
		private string $_tableName;

		public function __construct(object $db, string $tableName, string $entityClass) {
			$this->db         = $db;
			$this->_tableName = $tableName;
		}

		protected function getTableName(): string {
			return $this->_tableName;
		}

		public function insert(Entity $entity): Entity {
			return $entity;
		}

		public function delete(Entity $entity): Entity {
			return $entity;
		}

		/** @return T */
		protected function findEntity(object $qb): Entity {
			throw new DoesNotExistException('Not found');
		}

		/** @return T[] */
		protected function findEntities(object $qb): array {
			return [];
		}
	}
}

namespace OCP\DB\QueryBuilder {
	interface IQueryBuilder {
		/** Type constants mirroring Nextcloud's IQueryBuilder. */
		public const PARAM_STR  = \PDO::PARAM_STR;
		public const PARAM_INT  = \PDO::PARAM_INT;
		public const PARAM_BOOL = \PDO::PARAM_BOOL;
		public const PARAM_NULL = \PDO::PARAM_NULL;
		public const PARAM_STR_ARRAY  = 101;
		public const PARAM_INT_ARRAY  = 102;

		public function select(mixed ...$selects): static;
		public function from(string $table, ?string $alias = null): static;
		public function where(mixed $predicate): static;
		public function orderBy(string $sort, ?string $order = null): static;
		public function expr(): IExpressionBuilder;
		public function createNamedParameter(mixed $value, mixed $type = null, ?string $placeHolder = null): string;
	}

	interface IExpressionBuilder {
		public function eq(string $x, mixed $y): string;
	}
}

namespace OCP {
	interface IDBConnection {
		public function getQueryBuilder(): \OCP\DB\QueryBuilder\IQueryBuilder;
	}

	interface IConfig {
		public function getAppValue(string $appName, string $key, string $default = ''): string;
		public function setAppValue(string $appName, string $key, string $value): void;
	}

	interface IRequest {
		public function getParam(string $key, mixed $default = null): mixed;
	}

	interface IUser {
		public function getUID(): string;
	}

	interface IUserSession {
		public function getUser(): ?IUser;
	}

	interface IL10N {
		public function t(string $text, mixed $parameters = []): string;
	}

	interface IURLGenerator {
		public function imagePath(string $appName, string $file): string;
		public function linkToRoute(string $routeName, array $arguments = []): string;
	}
}

namespace OCP\Security {
	interface ISecureRandom {
		public const CHAR_LOWER  = 'abcdefghijklmnopqrstuvwxyz';
		public const CHAR_DIGITS = '0123456789';

		public function generate(int $length, string $characters = ''): string;
	}
}

namespace OCP\AppFramework {
	use OCP\IRequest;

	abstract class Controller {
		public function __construct(string $appName, IRequest $request) {
		}
	}

	abstract class App {
		public function __construct(string $appName, array $urlParams = []) {
		}
	}

	/**
	 * HTTP status code constants – mirrors OCP\AppFramework\Http in Nextcloud.
	 * (In Nextcloud there is both a *class* OCP\AppFramework\Http and a
	 *  *namespace* OCP\AppFramework\Http – PHP allows this.)
	 */
	class Http {
		public const STATUS_OK          = 200;
		public const STATUS_CREATED     = 201;
		public const STATUS_NO_CONTENT  = 204;
		public const STATUS_BAD_REQUEST = 400;
		public const STATUS_UNAUTHORIZED = 401;
		public const STATUS_FORBIDDEN   = 403;
		public const STATUS_NOT_FOUND   = 404;
		public const STATUS_INTERNAL_SERVER_ERROR = 500;
	}
}

namespace OCP\AppFramework\Http {
	class DataResponse {
		private mixed $data;
		private int   $status;

		public function __construct(mixed $data = [], int $status = 200) {
			$this->data   = $data;
			$this->status = $status;
		}

		public function getData(): mixed {
			return $this->data;
		}

		public function getStatus(): int {
			return $this->status;
		}
	}

	class TemplateResponse {
		public const RENDER_AS_BLANK = 'blank';

		private string $appName;
		private string $templateName;
		private array  $params;
		private string $renderAs;

		public function __construct(
			string $appName,
			string $templateName,
			array  $params   = [],
			string $renderAs = 'user'
		) {
			$this->appName      = $appName;
			$this->templateName = $templateName;
			$this->params       = $params;
			$this->renderAs     = $renderAs;
		}

		public function getParams(): array {
			return $this->params;
		}

		public function getTemplateName(): string {
			return $this->templateName;
		}

		public function getRenderAs(): string {
			return $this->renderAs;
		}
	}
}

namespace OCP\AppFramework\Http\Attribute {
	#[\Attribute(\Attribute::TARGET_METHOD)]
	class NoAdminRequired {
	}

	#[\Attribute(\Attribute::TARGET_METHOD)]
	class NoCSRFRequired {
	}
}

namespace OCP\AppFramework\Bootstrap {
	interface IRegistrationContext {
	}

	interface IBootContext {
	}

	interface IBootstrap {
		public function register(IRegistrationContext $context): void;
		public function boot(IBootContext $context): void;
	}
}

namespace OCP\Settings {
	interface ISettings {
		public function getForm(): \OCP\AppFramework\Http\TemplateResponse;
		public function getSection(): string;
		public function getPriority(): int;
	}

	interface IIconSection {
		public function getIcon(): string;
		public function getID(): string;
		public function getName(): string;
		public function getPriority(): int;
	}
}

namespace OCP\Migration {
	interface IOutput {
	}

	abstract class SimpleMigrationStep {
	}
}

namespace OCP\DB {
	interface ISchemaWrapper {
		public function hasTable(string $tableName): bool;
		public function createTable(string $tableName): object;
	}

	class Types {
		public const INTEGER = 'integer';
		public const STRING  = 'string';
	}
}
