<?php
namespace Logger\PushoverLogger;

class CurlTransportClient implements TransportClient {
	private string $endpoint;
	/** @var array<int, mixed> */
	private array $curlOpts;

	/**
	 * @param array<int, mixed> $curlOpts
	 */
	public function __construct(string $endpoint, array $curlOpts = []) {
		$this->endpoint = $endpoint;
		$this->curlOpts = $curlOpts;
	}

	/**
	 * @param array<string, scalar|null> $data
	 */
	public function post(array $data): void {
		$ch = curl_init();
		if ($ch === false) {
			return;
		}
		$this->curlOpts[CURLOPT_URL] = $this->endpoint;
		$this->curlOpts[CURLOPT_POSTFIELDS] = $data;
		$this->curlOpts[CURLOPT_RETURNTRANSFER] = true;
		curl_setopt_array($ch, $this->curlOpts);
		curl_exec($ch);
		curl_close($ch);
	}
}
