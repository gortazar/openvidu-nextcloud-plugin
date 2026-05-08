<?php

declare(strict_types=1);

namespace OCA\OpenViduIntegration\Tests\Unit\Controller;

use OCA\OpenViduIntegration\Controller\AdminController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for AdminController.
 */
class AdminControllerTest extends TestCase {
	private AdminController $controller;

	/** @var IConfig&MockObject */
	private IConfig $config;

	protected function setUp(): void {
		$request        = $this->createMock(IRequest::class);
		$this->config   = $this->createMock(IConfig::class);

		$this->controller = new AdminController($request, $this->config);
	}

	// -----------------------------------------------------------------------
	// saveSettings()
	// -----------------------------------------------------------------------

	public function testSaveSettingsReturns200OnValidUrl(): void {
		$this->config->expects($this->exactly(2))
			->method('setAppValue');

		$response = $this->controller->saveSettings('https://meet.example.com');

		$this->assertInstanceOf(DataResponse::class, $response);
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function testSaveSettingsReturnsNormalizedUrl(): void {
		$this->config->method('setAppValue');

		$data = $this->controller->saveSettings('https://meet.example.com/')->getData();

		// Trailing slash should be stripped
		$this->assertSame('https://meet.example.com', $data['openvidu_meet_url']);
	}

	public function testSaveSettingsAllowsEmptyUrl(): void {
		$this->config->expects($this->exactly(2))
			->method('setAppValue');

		$response = $this->controller->saveSettings('');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function testSaveSettingsReturns400ForInvalidUrl(): void {
		$this->config->expects($this->never())->method('setAppValue');

		$response = $this->controller->saveSettings('not-a-url');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertArrayHasKey('error', $response->getData());
	}

	public function testSaveSettingsTrimsWhitespace(): void {
		$captured = null;
		$this->config->method('setAppValue')
			->willReturnCallback(function (string $app, string $key, string $value) use (&$captured) {
				if ($key === 'openvidu_meet_url') {
					$captured = $value;
				}
			});

		$this->controller->saveSettings('  https://meet.example.com  ');

		$this->assertSame('https://meet.example.com', $captured);
	}

	public function testSaveSettingsReturnsStoredUrl(): void {
		$this->config->method('setAppValue');

		$data = $this->controller->saveSettings('https://my-meet.example.org')->getData();

		$this->assertSame('https://my-meet.example.org', $data['openvidu_meet_url']);
	}

	public function testSaveSettingsStoresApiKey(): void {
		$capturedKey = null;
		$this->config->method('setAppValue')
			->willReturnCallback(function (string $app, string $key, string $value) use (&$capturedKey) {
				if ($key === 'openvidu_api_key') {
					$capturedKey = $value;
				}
			});

		$response = $this->controller->saveSettings('https://meet.example.com', 'my-secret-key');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame('my-secret-key', $capturedKey);
		$this->assertSame('my-secret-key', $response->getData()['openvidu_api_key']);
	}

	public function testSaveSettingsTrimsApiKey(): void {
		$capturedKey = null;
		$this->config->method('setAppValue')
			->willReturnCallback(function (string $app, string $key, string $value) use (&$capturedKey) {
				if ($key === 'openvidu_api_key') {
					$capturedKey = $value;
				}
			});

		$this->controller->saveSettings('https://meet.example.com', '  my-key  ');

		$this->assertSame('my-key', $capturedKey);
	}

	public function testSaveSettingsRejectsJavascriptScheme(): void {
		$this->config->expects($this->never())->method('setAppValue');

		// javascript: is not a valid URL per filter_var
		$response = $this->controller->saveSettings('javascript:alert(1)');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testSaveSettingsRejectsFtpScheme(): void {
		$this->config->expects($this->never())->method('setAppValue');

		// ftp:// passes filter_var(FILTER_VALIDATE_URL) but must be rejected
		// by the explicit http/https scheme check.
		$response = $this->controller->saveSettings('ftp://files.example.com');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testSaveSettingsAcceptsHttpsUrl(): void {
		$this->config->method('setAppValue');

		$response = $this->controller->saveSettings('https://secure.example.com');
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}

	public function testSaveSettingsAcceptsHttpUrl(): void {
		$this->config->method('setAppValue');

		$response = $this->controller->saveSettings('http://internal.lan');
		$this->assertSame(Http::STATUS_OK, $response->getStatus());
	}
}
