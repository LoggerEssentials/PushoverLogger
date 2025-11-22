<?php

namespace Logger\PushoverLogger;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Transport client using PSR-18 HTTP client and PSR-17 factories.
 */
class Psr18TransportClient implements TransportClient {
	private string $endpoint;
	private ClientInterface $client;
	private RequestFactoryInterface $requestFactory;
	private StreamFactoryInterface $streamFactory;

	public function __construct(
		string $endpoint,
		ClientInterface $client,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface $streamFactory,
	) {
		$this->endpoint = $endpoint;
		$this->client = $client;
		$this->requestFactory = $requestFactory;
		$this->streamFactory = $streamFactory;
	}

	/**
	 * @param array<string, scalar|null> $data
	 */
	public function post(array $data): void {
		$bodyString = http_build_query($data, '', '&');
		$request = $this->requestFactory
			->createRequest('POST', $this->endpoint)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded');

		$stream = $this->streamFactory->createStream($bodyString);
		$request = $request->withBody($stream);

		$this->client->sendRequest($request);
	}
}

