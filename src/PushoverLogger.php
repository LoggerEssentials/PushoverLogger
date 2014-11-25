<?php
namespace Logger;

use Logger\Pushover\CurlTransportClient;
use Logger\Pushover\TransportClient;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class PushoverLogger extends AbstractLogger implements Logger {
	/**
	 * @var string[]
	 */
	private $parameters = array();
	/**
	 * @var TransportClient
	 */
	private $transportClient;

	/**
	 * @param string $user
	 * @param string $token
	 * @param array $parameters
	 * @param TransportClient $transportClient
	 */
	public function __construct($user, $token, array $parameters, TransportClient $transportClient = null) {
		$parameters['token'] = $token;
		$parameters['user'] = $user;
		$this->parameters = $parameters;
		if($transportClient === null) {
			$transportClient = new CurlTransportClient("https://api.pushover.net/1/messages.json");
		}
		$this->transportClient = $transportClient;
	}


	/**
	 * Logs with an arbitrary level.
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function log($level, $message, array $context = array()) {
		try {
			$parameters = $this->parameters;
			$parameters['priority'] = $this->convertLevelToPriority($level);
			if($parameters['priority']) {
				$parameters['expire'] = 3600;
				$parameters['retry'] = 120;
			}
			$parameters['message'] = $message;
			$this->push($parameters);
		} catch (\Exception $e) {
		}
	}

	/**
	 * @param string[] $parameters
	 */
	private function push($parameters) {
		try {
			$this->transportClient->post($parameters);
		} catch(\Exception $e) {
		}
	}

	/**
	 * @param string $level
	 * @return int
	 */
	private function convertLevelToPriority($level) {
		switch ($level) {
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
				return 2;
			case LogLevel::CRITICAL:
				return 1;
			case LogLevel::ERROR:
				return 0;
		}
		return -1;
	}
}
