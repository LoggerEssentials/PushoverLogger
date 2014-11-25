<?php
namespace Logger\Pushover;

interface TransportClient {
	/**
	 * @param array $data
	 * @return void
	 */
	public function post(array $data);
}