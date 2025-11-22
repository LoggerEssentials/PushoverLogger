PushoverLogger
==============

A PSR‑3 compatible logger that sends messages to Pushover.

Requirements
------------
- PHP >= 8.0
- `logger/essentials` (pulled via composer)
- One of the following transports:
  - Built‑in cURL (ext-curl)
  - PSR‑18 HTTP client + PSR‑17 factories (suggested: `psr/http-client`, `psr/http-factory`)

Installation
------------
Install via Composer in your application:

```
composer require logger/pushover
```

Basic Usage (cURL transport)
----------------------------
The simplest way uses the built‑in cURL transport. If no transport is provided, cURL is used automatically.

```php
<?php

use Logger\PushoverLogger;

$user  = 'your-user-or-group-key';
$token = 'your-application-token';

// Optional default parameters are passed through to Pushover API.
$params = [
    'title' => 'My App',
    // 'sound' => 'pushover',
    // 'device' => 'iphone',
];

// cURL transport is used by default if you don’t pass one in
$logger = new PushoverLogger($user, $token, $params);

// Log using PSR-3
$logger->emergency('Database is down');   // priority 2, adds retry/expire
$logger->alert('High latency detected');  // priority 2
$logger->critical('Cache unavailable');   // priority 1
$logger->error('Background job failed');  // priority 0
```

Using PSR‑18 Transport
----------------------
If you prefer a PSR‑18 client with PSR‑17 factories, use the provided transport.

Install a client and PSR‑7 implementation of your choice (examples):

```
composer require psr/http-client psr/http-factory nyholm/psr7 symfony/http-client
```

```php
<?php

use Logger\PushoverLogger;
use Logger\PushoverLogger\Psr18TransportClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$user  = 'your-user-or-group-key';
$token = 'your-application-token';
$params = ['title' => 'My App'];

$psr17  = new Psr17Factory();
$client = new Psr18Client(); // Any PSR-18 ClientInterface implementation

$transport = new Psr18TransportClient(
    'https://api.pushover.net/1/messages.json',
    $client,
    $psr17,  // RequestFactoryInterface
    $psr17   // StreamFactoryInterface
);

$logger = new PushoverLogger($user, $token, $params, $transport);
$logger->emergency('Production outage');
```

How priorities are mapped
-------------------------
- `emergency` and `alert` map to Pushover priority `2` (emergency) and automatically add `retry=120` and `expire=3600`.
- `critical` maps to `1`.
- `error` maps to `0`.
- Other PSR‑3 levels are sent with priority `-1` (no priority).

Passing Pushover parameters
---------------------------
Any extra values in the `$parameters` array are passed straight to the Pushover API (e.g. `title`, `sound`, `device`, `url`, `url_title`). Refer to https://pushover.net/api for the full list.

Error handling
--------------
This logger catches throwables internally and does not throw on delivery failures to avoid impacting your application’s control flow. Consider monitoring delivery via your transport/client as needed.

License
-------
MIT
