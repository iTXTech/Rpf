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

class Launcher{

	private $address;
	private $port;
	/** @var Interceptor */
	private $interceptor;
	/** @var Resolver */
	private $resolver;

	public function __construct(){
	}

	public function listen(string $address, int $port){
		$this->address = $address;
		$this->port = $port;
		return $this;
	}

	public function interceptor(Interceptor $interceptor){
		$this->interceptor = $interceptor;
		return $this;
	}

	public function resolver(Resolver $resolver){
		$this->resolver = $resolver;
		return $this;
	}

	public function launch() : Rpf{
		return new Rpf();
	}

}