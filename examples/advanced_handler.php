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

require_once "env.php";

use iTXTech\Rpf\{Launcher, Listener};
use iTXTech\SimpleFramework\Console\Logger;
use Swoole\{Atomic, Http\Request, Http\Response};

Logger::info("Constructing");
$launcher = (new Launcher())
	->listen("127.0.0.1", 8080)
	->verify(true)
	->handler(new class extends DefaultHandler{
		/** @var Atomic */
		private $requests;

		public function init(bool $ssl, bool $verify, string $uuid){
			parent::init($ssl, $verify, $uuid);
			$this->ssl = true;
			$this->requests = new Atomic(0);
		}

		public function request(Listener $listener, Request $request, Response $response) : bool{
			parent::request($listener, $request, $response);
			$this->requests->add(1);
			$response->header("Content-Type", "text/plain");
			$response->end("Total requests: " . $this->requests->get());
			return false;
		}
	});

load($launcher);
