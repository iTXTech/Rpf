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

namespace iTXTech\Rpf;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Process;

class Rpf{
	/** @var Process */
	private $proc;

	private $addr;
	private $port;
	/** @var Handler */
	private $handler;
	private $swooleOptions;
	private $ssl;

	public function __construct(string $addr, int $port, ?Handler $handler, array $swooleOptions, bool $ssl){
		Loader::getInstance()->addInstance($this);

		$handler = $handler ?? new Handler();
		$handler->ssl($ssl);

		$this->addr = $addr;
		$this->port = $port;
		$this->handler = $handler;
		$this->swooleOptions = $swooleOptions;
		$this->ssl = $ssl;
	}

	public function launch(){
		$addr = $this->addr;
		$port = $this->port;
		$handler = $this->handler;
		$swOpts = $this->swooleOptions;
		$ssl = $this->ssl;
		$this->proc = new Process(function() use ($addr, $port, $handler, $swOpts, $ssl){
			$server = new Server($addr, $port, SWOOLE_PROCESS, $ssl ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP);
			$server->set($swOpts);

			$server->on("start", function(Server $server){
				Logger::info(TextFormat::GREEN . "iTXTech Rpf is listening on " . $server->host . ":" . $server->port);
			});
			$server->on("request", function(Request $request, Response $response) use ($server, $handler, $ssl){
				$handler->request($request);
				$body = $handler->forward($request, $response);
				$handler->complete($request, $response, $body);
			});

			$server->start();
		});
		$this->proc->start();
	}

	public function shutdown(){
		$this->proc->close();
		Loader::getInstance()->removeInstance($this);
	}
}