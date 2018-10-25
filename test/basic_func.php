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

require_once "load_env.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\Rpf\{Interceptor, Resolver, Launcher};

Logger::info("Constructing");
$launcher = (new Launcher())
	->listen("127.0.0.1", 2333)
	->interceptor(new class() extends Interceptor{

	})
	->resolver(new class() extends Resolver{

	});

Logger::info("Launching");
$time = microtime(true);
$rpf = $launcher->launch();
Logger::info("Launched " . round((microtime(true) - $time) * 1000, 2) . " ms");
