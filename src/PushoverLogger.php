<?php

namespace Logger;

use Logger\PushoverLogger\CurlTransportClient;
use Logger\PushoverLogger\TransportClient;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class PushoverLogger extends AbstractLogger {
	/** @var array<string, null|scalar> */
	private array $parameters;
	private TransportClient $transportClient;

	/**
	 * @param string $user
	 * @param string $token
	 * @param array $parameters
	 * @param TransportClient $transportClient
	 */
	public function __construct($user, $token, array $parameters, ?TransportClient $transportClient = null) {
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
	 *
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function log($level, $message, array $context = []) {
		try {
			$parameters = $this->parameters;
			$parameters['priority'] = $this->convertLevelToPriority($level);
			// Only emergency priority (2) requires retry/expire according to Pushover API
			if($parameters['priority'] === 2) {
				$parameters['expire'] = 3600;
				$parameters['retry'] = 120;
			}
			$parameters['message'] = $message;
			$this->push($parameters);
		} catch(\Throwable $e) {
		}
	}

	/**
	 * @param string[] $parameters
	 */
	private function push($parameters) {
		try {
			$this->transportClient->post($parameters);
		} catch(\Throwable $e) {
		}
	}

	/**
	 * @param string $level
	 * @return int
	 */
	private function convertLevelToPriority($level) {
		return match ($level) {
			LogLevel::EMERGENCY, LogLevel::ALERT => 2,
			LogLevel::CRITICAL => 1,
			LogLevel::ERROR => 0,
			default => -1,
		};
	}
}
