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

namespace iTXTech\Rpf;

class Launcher{
	private $swooleOptions = [
		"worker_num" => 8
	];

	/** @var Listener[] */
	private $listeners = [];
	/** @var Handler */
	private $handler;
	private $verify = false;
	private $uuid;

	public function __construct(){
		$this->uuid = self::generateUuid();
	}

	/**
	 * Add a listener
	 *
	 * @param string|Listener|Listener[] $address
	 * @param int $port
	 * @param bool $ssl
	 *
	 * @return $this
	 */
	public function listen($address, int $port = 0, bool $ssl = false) : Launcher{
		if(is_array($address)){
			foreach($address as $addr){
				if($addr instanceof Listener){
					$this->listeners[] = $addr;
				}
			}
		}elseif($address instanceof Listener){
			$this->listeners[] = $address;
		}else{
			$this->listeners[] = new Listener($address, $port, $ssl);
		}
		return $this;
	}

	/**
	 * Set a custom Handler
	 *
	 * @param Handler $handler
	 *
	 * @return $this
	 */
	public function handler(Handler $handler) : Launcher{
		$this->handler = $handler;
		return $this;
	}

	/**
	 * Set swoole worker num
	 *
	 * @param int $n
	 *
	 * @return $this
	 */
	public function workers(int $n) : Launcher{
		$this->swooleOptions["worker_num"] = $n;
		return $this;
	}

	/**
	 * Set SSL Certificate and Private Key
	 *
	 * @param string $cert
	 * @param string $key
	 *
	 * @return $this
	 */
	public function ssl(string $cert, string $key) : Launcher{
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
	 * @param string $uuid
	 *
	 * @return $this
	 */
	public function verify(bool $verify, string $uuid = null) : Launcher{
		$this->verify = $verify;
		if($uuid !== null){
			$this->uuid = $uuid;
		}
		return $this;
	}

	/**
	 * Set extra swoole options
	 *
	 * @param array $opts
	 *
	 * @return $this
	 */
	public function swOpts(array $opts) : Launcher{
		$this->swooleOptions = array_merge($this->swooleOptions, $opts);
		return $this;
	}


	/**
	 * Build a Rpf instance
	 *
	 * @return Rpf
	 * @throws \Exception
	 */
	public function build() : Rpf{
		if(count($this->listeners) === 0){
			throw new \Exception("No listener.");
		}
		if($this->handler === null){
			throw new \Exception("Handler not set.");
		}
		foreach($this->listeners as $listener){
			if($listener->ssl and
				(!isset($this->swooleOptions["ssl_cert_file"]) or
					!isset($this->swooleOptions["ssl_key_file"]))){
				throw new \Exception("SSL certificate not set.");
			}
		}
		return new Rpf($this->listeners, $this->handler, $this->swooleOptions, $this->verify, $this->uuid);
	}

	/**
	 * Build and launch a Rpf instance
	 *
	 * @return Rpf
	 * @throws \Exception
	 */
	public function launch() : Rpf{
		$rpf = $this->build();
		$rpf->launch();
		return $rpf;
	}

	/**
	 * Generate UUID for a Rpf instance
	 *
	 * @return string
	 */
	public static function generateUuid() : string{
		return md5("iTXTech Rpf " . microtime(true));
	}
}
