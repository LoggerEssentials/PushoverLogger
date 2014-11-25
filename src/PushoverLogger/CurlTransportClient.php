<?php
namespace Logger\Pushover;

class CurlTransportClient implements TransportClient {
	/**
	 * @var string
	 */
	private $endpoint;
	/**
	 * @var array
	 */
	private $proxy;

	/**
	 * @param string $endpoint
	 * @param array $proxy
	 */
	public function __construct($endpoint, array $proxy = array()) {
		$this->endpoint = $endpoint;
		$this->proxy = $proxy;
	}

	/**
	 * @param array $data
	 */
	public function post(array $data) {
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->endpoint,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_RETURNTRANSFER => true
		));
		$this->initProxy($ch);
		curl_exec($ch);
		curl_close($ch);
	}

	/**
	 * @param resource $ch
	 */
	private function initProxy($ch) {
		$proxy = $this->proxy;
		$proxy = array_merge(array('type' => 'http', 'authtype' => 'basic', 'host' => null, 'port' => null, 'auth' => null), $proxy);
		if($proxy['host'] === null) {
			return;
		}
		curl_setopt($ch, CURLOPT_PROXYTYPE, $this->getProxyType($proxy['type']));
		curl_setopt($ch, CURLOPT_HTTPAUTH, $this->getProxyAuthType($proxy['authtype']));
		if($proxy['host'] !== null && $proxy['port'] !== null) {
			curl_setopt($ch, CURLOPT_PROXY, "{$proxy['host']}:{$proxy['port']}");
			curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
		}
		if($proxy['host'] !== null && $proxy['port'] !== null) {
			curl_setopt($ch, CURLOPT_PROXY, "{$proxy['host']}:{$proxy['port']}");
			curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
		}
		if($proxy['auth'] !== null) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['auth']);
		}
	}

	/**
	 * @param string $type
	 * @return int
	 * @throws \Exception
	 */
	private function getProxyType($type) {
		if($type === 'http') {
			return CURLPROXY_HTTP;
		}
		if($type === 'socks5') {
			return CURLPROXY_SOCKS5;
		}
		throw new \Exception("Proxy type not available: {$type}");
	}

	/**
	 * @param string $type
	 * @return int
	 * @throws \Exception
	 */
	private function getProxyAuthType($type) {
		if($type === 'basic') {
			return CURLAUTH_BASIC;
		}
		if($type === 'digest') {
			return CURLAUTH_DIGEST;
		}
		if($type === 'gssnegotiate') {
			return CURLAUTH_GSSNEGOTIATE;
		}
		if($type === 'ntlm') {
			return CURLAUTH_NTLM;
		}
		throw new \Exception("Proxy type not available: {$type}");
	}
}