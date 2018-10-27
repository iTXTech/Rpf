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
	private $swooleOptions = [
		"worker_num" => 8
	];

	private $address;
	private $port;
	/** @var Handler */
	private $handler;
	private $ssl = false;
	private $verify = false;
	private $uniqueVerification;

	public function __construct(){
	}

	/**
	 * Set which address and port to listen on
	 *
	 * @param string $address
	 * @param int $port
	 * @return $this
	 */
	public function listen(string $address, int $port){
		$this->address = $address;
		$this->port = $port;
		return $this;
	}

	/**
	 * Set a custom Handler
	 *
	 * @param Handler $handler
	 * @return $this
	 */
	public function handler(Handler $handler){
		$this->handler = $handler;
		return $this;
	}

	/**
	 * Set swoole worker num
	 *
	 * @param int $n
	 * @return $this
	 */
	public function workers(int $n){
		$this->swooleOptions["worker_num"] = $n;
		return $this;
	}

	/**
	 * Set SSL Certificate and Private Key
	 *
	 * @param string $cert
	 * @param string $key
	 * @return $this
	 */
	public function ssl(string $cert, string $key){
		$this->ssl = true;
		$this->swooleOptions["ssl_cert_file"] = $cert;
		$this->swooleOptions["ssl_key_file"] = $key;
		return $this;
	}

	/**
	 * Verify HTTP Request or not
	 * Enable this feature will add an extra header to check
	 * whether the Request is being sent back to Rpf.
	 *
	 * @param bool $verify
	 * @param string $uniqueVerification
	 * @return $this
	 */
	public function verify(bool $verify, string $uniqueVerification = null){
		$this->verify = $verify;
		$this->uniqueVerification = $uniqueVerification ?? self::generateUniqueVerification();
		return $this;
	}

	/**
	 * Set extra swoole options
	 *
	 * @param array $opts
	 * @return $this
	 */
	public function swOpts(array $opts){
		$this->swooleOptions = array_merge($this->swooleOptions, $opts);
		return $this;
	}

	/**
	 * Build a Rpf instance
	 *
	 * @return Rpf
	 */
	public function build(): Rpf{
		return new Rpf($this->address, $this->port, $this->handler, $this->swooleOptions,
			$this->ssl, $this->verify, $this->uniqueVerification);
	}

	/**
	 * Build and launch a Rpf instance
	 *
	 * @return Rpf
	 */
	public function launch(): Rpf{
		$rpf = $this->build();
		$rpf->launch();
		return $rpf;
	}

	public static function generateUniqueVerification(): string {
		return md5("iTXTech Rpf " . microtime(true));
	}

}