<?php

/*
 *
 * iTXTech Rpf
 *
 * Copyright (C) 2018 iTX Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

/*
 * cert.crt and private.key must exist
 */

require_once "load_env.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\Rpf\{Handler, Launcher};
use Swoole\Http\{Request, Response};
use Swoole\Coroutine\Http\Client;

Logger::info("Constructing");
$launcher = (new Launcher())
	->listen("127.0.0.1", 443)
	->ssl("cert.crt", "private.key")
	->handler(new class() extends Handler{
		public function request(Request $request){
			Logger::info("Got request from " . $request->server["remote_addr"] . " to " .
				$request->header["host"] . $request->server["request_uri"]);
		}

		public function complete(Request $request, Response $response, string $body){
			Logger::info("Got response from " . $request->header["host"] . $request->server["request_uri"] .
				" len: " . strlen($body));
		}

		public function response(Request $request, Response $response, Client $client){
			$response->header["X-Powered-By"] = "iTXTech Rpf";
			$client->body .= "\n<!-- Powered by iTXTech Rpf --!>";
		}
	});

load($launcher);
