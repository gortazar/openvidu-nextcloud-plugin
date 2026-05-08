<?php

declare(strict_types=1);

return [
	'routes' => [
		// Page routes
		['name' => 'page#index',  'url' => '/',              'verb' => 'GET'],
		['name' => 'page#room',   'url' => '/room/{token}',  'verb' => 'GET'],

		// REST API routes (require login; CSRF handled via bearer token / header)
		['name' => 'api#getRooms',    'url' => '/api/rooms',          'verb' => 'GET'],
		['name' => 'api#createRoom',  'url' => '/api/rooms',          'verb' => 'POST'],
		['name' => 'api#deleteRoom',  'url' => '/api/rooms/{token}',  'verb' => 'DELETE'],

		// Admin API
		['name' => 'admin#saveSettings', 'url' => '/api/admin/settings', 'verb' => 'POST'],
	],
];
