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

require_once "../sf/autoload.php";

use iTXTech\Rpf\{Handler, Launcher, Rpf};
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Module\ModuleManager;
use Swoole\Coroutine\Http\Client;
use Swoole\Http\{Request, Response};

Initializer::initTerminal(true);

Logger::info("iTXTech Rpf Test Framework: " . basename($argv[0], ".php"));
Logger::info("Loading iTXTech Rpf");

global $classLoader;
try{
	$moduleManager = new ModuleManager($classLoader, __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR, __DIR__ . "data");
	$moduleManager->loadModules();
}catch(Throwable $e){
	Logger::logException($e);
}

function load(Launcher $launcher){
	Logger::info("Launching");
	$time = microtime(true);
	$launcher->launch();
	Logger::info("Launched " . round((microtime(true) - $time) * 1000, 2) . " ms");

	while(true) ;
}

class DefaultHandler extends Handler{
	public function request(Request $request, Response $response) : bool {
		if(parent::request($request, $response)){
			Logger::info("Got request from " . $request->server["remote_addr"] . " to " .
				$request->header["host"] . $request->server["request_uri"]);
			return true;
		}
		return false;
	}

	public function complete(Request $request, Response $response, string $body){
		Logger::info("Got response from " . $request->header["host"] . $request->server["request_uri"] .
			" len: " . strlen($body));
	}

	public function invalid(Request $request, Response $response, int $reason){
		switch($reason){
			case Rpf::INVALID_REQUEST_LOOP:
				$response->header["Content-Type"] = "text/plain";
				$response->end("Sending request to current Rpf instance again.");
				break;
		}
	}

	public function response(Request $request, Response $response, Client $client){
		$client->headers["Server"] = "iTXTech Rpf";
		$client->body .= "\n<!-- Powered by iTXTech Rpf --!>";
	}
}
