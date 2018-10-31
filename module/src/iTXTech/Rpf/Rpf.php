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
use Swoole\Server\Port;

class Rpf{
	public const EXTRA_HEADER = "rpf-request-verification";
	public const INVALID_REQUEST_LOOP = 0;

	/** @var Process */
	private $proc;
	/** @var Handler */
	private $handler;

	/** @var Listener[] */
	public $listeners;
	public $swooleOptions;
	public $uuid;

	public function __construct(array $listeners, ?Handler $handler, array $swooleOptions, bool $verify, string $uuid){
		$this->listeners = $listeners;
		$this->handler = $handler ?? new Handler();
		$this->handler->init($this->listeners[0]->ssl, $verify, $uuid);
		$this->swooleOptions = $swooleOptions;
		$this->uuid = $uuid;
		Loader::getInstance()->addInstance($this);
	}

	public function launch(){
		$listeners = $this->listeners;
		$handler = $this->handler;
		$swOpts = $this->swooleOptions;
		$uuid = $this->uuid;
		$this->proc = new Process(function(Process $process) use ($listeners, $handler, $swOpts, $uuid){
			try{
				$mainListener = array_shift($listeners);
				$server = new Server($mainListener->address, $mainListener->port, SWOOLE_PROCESS,
					$mainListener->ssl ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP);
				$server->set($swOpts);

				while(($listener = array_shift($listeners)) !== null){
					$port = $server->listen($listener->address, $listener->port,
						$listener->ssl ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP);
					if($port === false){
						Logger::error("iTXTech Rpf ($uuid) cannot bind to " . $listener["addr"] . ":" . $listener["port"]);
					}else{
						/** @var Port $port */
						$port->on("request", function(Request $request, Response $response) use ($server, $handler, $listener){
							if($handler->request($listener, $request, $response)){
								$handler->complete($listener, $request, $response, $handler->forward($listener, $request, $response));
							}
						});
					}
				}

				$server->on("start", function(Server $server) use ($uuid){
					foreach($server->ports as $port){
						Logger::info(TextFormat::GREEN . "iTXTech Rpf ($uuid) is listening on " . $port->host . ":" . $port->port);
					}
				});
				$server->on("request", function(Request $request, Response $response) use ($server, $handler, $mainListener){
					if($handler->request($mainListener, $request, $response)){
						$handler->complete($mainListener, $request, $response, $handler->forward($mainListener, $request, $response));
					}
				});

				$server->start();
			}catch(\Throwable $e){
				Logger::logException($e);
			}
		});
		$this->proc->start();
	}

	public function shutdown(){
		$this->proc->close();
		Loader::getInstance()->removeInstance($this);
	}
}