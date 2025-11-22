<?php

use Logger\PushoverLogger\Psr18TransportClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class Psr18TransportClientTest extends TestCase {
	public function testSendsFormEncodedRequestViaPsr18(): void {
		if(!interface_exists(ClientInterface::class) ||
			!interface_exists(RequestFactoryInterface::class) ||
			!interface_exists(StreamFactoryInterface::class)) {
			$this->markTestSkipped('psr/http-client and psr/http-factory are not installed');
		}

		$client = $this->createMock(ClientInterface::class);
		$reqFactory = $this->createMock(RequestFactoryInterface::class);
		$streamFactory = $this->createMock(StreamFactoryInterface::class);

		$endpoint = 'https://api.pushover.net/1/messages.json';
		$data = ['message' => 'Hi', 'priority' => 2];
		$expectedBody = http_build_query($data, '', '&');

		$request = $this->createMock(RequestInterface::class);
		// make withHeader/withBody return the same mock to allow chaining
		$request->method('withHeader')->willReturnSelf();
		$request->method('withBody')->willReturnSelf();

		$reqFactory
			->expects($this->once())
			->method('createRequest')
			->with('POST', $endpoint)
			->willReturn($request);

		$stream = $this->createMock(StreamInterface::class);
		$streamFactory
			->expects($this->once())
			->method('createStream')
			->with($this->equalTo($expectedBody))
			->willReturn($stream);

		$client
			->expects($this->once())
			->method('sendRequest')
			->with($this->identicalTo($request));

		$transport = new Psr18TransportClient($endpoint, $client, $reqFactory, $streamFactory);
		$transport->post($data);
		$this->assertTrue(true); // @phpstan-ignore-line
	}
}

