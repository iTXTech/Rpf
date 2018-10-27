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

use Swoole\Coroutine\Http\Client;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Handler{
	protected $ssl = false;

	/**
	 * Set SSL enabled or not
	 * You can pretend SSL is enabled when downgrading HTTPS to HTTP
	 *
	 * @param bool $ssl
	 */
	public function ssl(bool $ssl){
		$this->ssl = $ssl;
	}

	/**
	 * Modify HTTP Request before sending to upstream server
	 *
	 * @param Request $request
	 */
	public function request(Request $request){
	}

	/**
	 * Resolve host to IP address
	 * Default is to be resolved by swoole
	 *
	 * @param string $host
	 * @return string
	 */
	public function resolve(string $host): string{
		return $host;
	}

	/**
	 * Forward HTTP Request to upstream server
	 * Do not override this method before you know how it works
	 *
	 * @param Request $request
	 * @param Response $response
	 * @return string
	 */
	public function forward(Request $request, Response $response): string{
		$uri = $request->server["request_uri"];
		if(isset($request->server["query_string"])){
			$uri .= "?" . $request->server["query_string"];
		}
		$headerHost = explode(":", $request->header["host"]);
		$host = $this->resolve($headerHost[0]);
		$port = isset($headerHost[1]) ? $headerHost[1] : ($this->ssl ? "443" : "80");

		$header = $request->header;
		$header["host"] = $host . ":" . $port;
		$client = new Client($host, $port, $this->ssl);
		$client->setHeaders($header);
		if($request->server["request_method"] === "GET"){
			$client->get($uri);
		}elseif($request->server["request_method"] === "POST"){
			$client->post($uri, $request->post);
		}

		$this->response($request, $response, $client);

		unset($client->headers["content-length"],
			$client->headers["content-encoding"]);

		foreach($client->headers as $k => $header){
			$response->header($k, $header);
		}

		$len = 0;
		while($len < strlen($client->body)){
			$l = $len + 1024 * 1024;
			$response->write(substr($client->body, $len, $l));
			$len = $l;
		}

		$response->end();

		return $client->body;
	}

	/**
	 * Modify HTTP Response before sending to client
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param Client $client
	 */
	public function response(Request $request, Response $response, Client $client){
	}

	/**
	 * Analyze/Record HTTP Session after it has completed
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param string $body
	 */
	public function complete(Request $request, Response $response, string $body){
	}
}