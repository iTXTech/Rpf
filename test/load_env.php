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

use iTXTech\SimpleFramework\Console\Logger;

Initializer::initTerminal(true);

Logger::info("iTXTech Rpf Test Framework: " . basename($argv[0],".php"));
Logger::info("Loading iTXTech Rpf");

global $classLoader;
try{
	$moduleManager = new \iTXTech\SimpleFramework\Module\ModuleManager($classLoader, __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR, __DIR__ . "data");
	$moduleManager->loadModules();
} catch(Throwable $e){
	Logger::logException($e);
}
