<?php

use Logger\PushoverLogger\TransportClient;
use PHPUnit\Framework\TestCase;

final class TransportClientInterfaceTest extends TestCase {
	public function testInterfaceExistsAndHasPostMethod(): void {
		$fqcn = TransportClient::class;
		$this->assertTrue(interface_exists($fqcn), 'TransportClient interface should exist');

		$ref = new ReflectionClass($fqcn);
		$this->assertTrue($ref->hasMethod('post'));
		$method = $ref->getMethod('post');
		$this->assertTrue($method->isPublic());
		$this->assertSame(1, $method->getNumberOfParameters());
	}
}
