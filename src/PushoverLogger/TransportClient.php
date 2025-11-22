<?php
namespace Logger\PushoverLogger;

interface TransportClient {
	/**
	 * @param array<string, scalar|null> $data
	 */
	public function post(array $data): void;
}
