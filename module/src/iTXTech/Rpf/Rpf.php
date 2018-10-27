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
	public const USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36";

	/** @var Process */
	private $proc;

	public function __construct(string $addr, int $port, ?Handler $i, array $swSet, bool $ssl){
		Loader::getInstance()->addInstance($this);

		$i = $i ?? new Handler();
		$i->ssl($ssl);
		$this->proc = new Process(function() use ($addr, $port, $i, $swSet, $ssl){
			$server = new Server($addr, $port, SWOOLE_PROCESS, $ssl ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP);
			$server->set($swSet);

			$server->on("start", function(Server $server){
				Logger::info(TextFormat::GREEN . "iTXTech Rpf is listening on " . $server->host . ":" . $server->port);
			});
			$server->on("request", function(Request $request, Response $response) use ($server, $i, $ssl){
				$i->request($request);
				$body = $i->forward($request, $response);
				$i->complete($request, $response, $body);
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