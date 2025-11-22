<?php

namespace Logger;

use Logger\Common\AbstractLoggerAware;
use Logger\PushoverLogger\CurlTransportClient;
use Logger\PushoverLogger\TransportClient;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * @phpstan-import-type TLogLevel from AbstractLoggerAware
 * @phpstan-import-type TLogMessage from AbstractLoggerAware
 * @phpstan-import-type TLogContext from AbstractLoggerAware
 */
class PushoverLogger extends AbstractLogger {
	/** @var array<string, scalar|null> */
	private array $parameters;
	private TransportClient $transportClient;

	/**
	 * @param array<string, scalar|null> $parameters
	 */
	public function __construct(string $user, string $token, array $parameters, ?TransportClient $transportClient = null) {
		/** @var array<string, scalar|null> $parameters */
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
	 * @param TLogLevel $level
	 * @param TLogMessage $message
	 * @param TLogContext $context
	 */
	public function log($level, $message, array $context = []): void {
		try {
			$parameters = $this->parameters;
			$parameters['priority'] = $this->convertLevelToPriority((string) $level);
			// Only emergency priority (2) requires retry/expire according to Pushover API
			if($parameters['priority'] === 2) {
				$parameters['expire'] = 3600;
				$parameters['retry'] = 120;
			}
			$parameters['message'] = (string) $message;
			$this->push($parameters);
		} catch(\Throwable $e) {
		}
	}

	/**
	 * @param array<string, scalar|null> $parameters
	 */
	private function push(array $parameters): void {
		try {
			$this->transportClient->post($parameters);
		} catch(\Throwable $e) {
		}
	}

	private function convertLevelToPriority(string $level): int {
		return match ($level) {
			LogLevel::EMERGENCY, LogLevel::ALERT => 2,
			LogLevel::CRITICAL => 1,
			LogLevel::ERROR => 0,
			default => -1,
		};
	}
}
