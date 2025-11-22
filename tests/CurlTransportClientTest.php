<?php

use Logger\PushoverLogger\CurlTransportClient;
use PHPUnit\Framework\TestCase;

final class CurlTransportClientTest extends TestCase {
	public function testPostDoesNotThrowWhenCurlAvailable(): void {
		if(!\function_exists('curl_init')) {
			$this->markTestSkipped('cURL extension is not available');
		}

		$client = new CurlTransportClient('https://example.com');
		// Should not throw even if endpoint is unreachable; method ignores result
		$client->post(['message' => 'hello']);
		$this->assertTrue(true);
	}
}
