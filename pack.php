<?php

/*
 *
 * iTXTech Rpf
 *
 * Copyright (C) 2018-2019 iTX Technologies
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

require_once "sfloader.php";

use iTXTech\Rpf\Loader;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\Packer;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal(true);

try{
	$moduleManager = new ModuleManager(Initializer::getClassLoader(), __DIR__ . DIRECTORY_SEPARATOR,
		__DIR__ . DIRECTORY_SEPARATOR);
	$moduleManager->loadModules();
}catch(Throwable $e){
	Logger::logException($e);
}

Loader::getInstance()->pack(Packer::VARIANT_TYPICAL, "./", $file = "iTXTech_Rpf.phar");
$phar = new Phar($file);
$metadata = $phar->getMetadata();
$metadata["revision"] = Util::getLatestGitCommitId("." . DIRECTORY_SEPARATOR);
$phar->setMetadata($metadata);
