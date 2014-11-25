<?php
namespace Logger\PushoverLogger;

class CurlTransportClient implements TransportClient {
	/**
	 * @var string
	 */
	private $endpoint;
	/**
	 * @var array
	 */
	private $curlOpts;

	/**
	 * @param string $endpoint
	 * @param array $curlOpts
	 */
	public function __construct($endpoint, array $curlOpts = array()) {
		$this->endpoint = $endpoint;
		$this->curlOpts = $curlOpts;
	}

	/**
	 * @param array $data
	 */
	public function post(array $data) {
		$ch = curl_init();
		$this->curlOpts[CURLOPT_URL] = $this->endpoint;
		$this->curlOpts[CURLOPT_POSTFIELDS] = $data;
		$this->curlOpts[CURLOPT_RETURNTRANSFER] = true;
		curl_setopt_array($ch, $this->curlOpts);
		$response = curl_exec($ch);
		curl_close($ch);
	}
}