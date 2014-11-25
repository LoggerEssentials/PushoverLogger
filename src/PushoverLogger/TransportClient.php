<?php
namespace Logger\PushoverLogger;

interface TransportClient {
	/**
	 * @param array $data
	 * @return void
	 */
	public function post(array $data);
}