<?php

use Logger\PushoverLogger;
use Logger\PushoverLogger\TransportClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class FakeTransportClient implements TransportClient {
	/** @var array<int, array<string, mixed>> */
	public $posts = [];

	public function post(array $data): void {
		$this->posts[] = $data;
	}
}

final class PushoverLoggerTest extends TestCase {
	public function testEmergencyAddsRetryAndExpire(): void {
		$fake = new FakeTransportClient();
		$logger = new PushoverLogger('user-id', 'app-token', ['title' => 'My Title'], $fake);

		$logger->log(LogLevel::EMERGENCY, 'EMERGENCY MESSAGE');

		$this->assertCount(1, $fake->posts);
		$data = $fake->posts[0];

		$this->assertSame('app-token', $data['token']);
		$this->assertSame('user-id', $data['user']);
		$this->assertSame('EMERGENCY MESSAGE', $data['message']);
		$this->assertSame(2, $data['priority']);
		$this->assertSame(3600, $data['expire']);
		$this->assertSame(120, $data['retry']);
		$this->assertSame('My Title', $data['title']);
	}

	public function testCriticalHasPriorityOneWithoutRetryExpire(): void {
		$fake = new FakeTransportClient();
		$logger = new PushoverLogger('u', 't', [], $fake);

		$logger->log(LogLevel::CRITICAL, 'CRIT');

		$this->assertCount(1, $fake->posts);
		$data = $fake->posts[0];
		$this->assertSame(1, $data['priority']);
		$this->assertArrayNotHasKey('expire', $data);
		$this->assertArrayNotHasKey('retry', $data);
	}

	public function testErrorHasPriorityZeroWithoutRetryExpire(): void {
		$fake = new FakeTransportClient();
		$logger = new PushoverLogger('u', 't', [], $fake);

		$logger->log(LogLevel::ERROR, 'ERR');

		$this->assertCount(1, $fake->posts);
		$data = $fake->posts[0];
		$this->assertSame(0, $data['priority']);
		$this->assertArrayNotHasKey('expire', $data);
		$this->assertArrayNotHasKey('retry', $data);
	}

	public function testUnknownLevelBecomesMinusOnePriority(): void {
		$fake = new FakeTransportClient();
		$logger = new PushoverLogger('u', 't', [], $fake);

		$logger->log(LogLevel::NOTICE, 'NOTICE');

		$this->assertCount(1, $fake->posts);
		$data = $fake->posts[0];
		$this->assertSame(-1, $data['priority']);
		$this->assertArrayNotHasKey('expire', $data);
		$this->assertArrayNotHasKey('retry', $data);
	}
}
