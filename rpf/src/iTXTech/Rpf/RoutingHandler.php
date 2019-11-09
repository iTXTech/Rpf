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

use Swoole\Coroutine\Http\Client;
use Swoole\Http\Request;
use Swoole\Http\Response;

class RoutingHandler extends Handler{
	public function processHostAndUri(Listener $listener, string &$host, string &$uri, bool &$ssl){
		$temp = explode("/", str_replace("//", "/", $uri));
		array_shift($temp);
		$host = array_shift($temp);
		$uri = "/" . implode("/", $temp);
		$ssl = true;
	}

	public function response(Listener $listener, Request $request, Response $response, Client $client){
		$client->body = self::alterAddr($listener, $client->body);
		$client->body = str_replace("href=\"//", "href=\"" . self::getPrefix($listener) . "/", $client->body);
		$client->body = str_replace("href=\"/", "href=\"" . self::getPrefix($listener) . "/" .
			explode("/", str_replace("//", "/", $request->server["request_uri"]))[1] . "/", $client->body);
		if(isset($client->headers["location"])){
			$client->headers["location"] = self::alterAddr($listener, $client->headers["location"]);
		}
	}

	public function request(Listener $listener, Request $request, Response $response) : bool{
		$result = parent::request($listener, $request, $response);
		if(isset($request->header["referer"])){
			$request->header["referer"] = str_replace(self::getPrefix($listener), "http://", $request->header["referer"]);
		}
		return $result;
	}

	private static function alterAddr(Listener $listener, string $str) : string{
		return str_replace(["http://", "https://"], self::getPrefix($listener) . "/", $str);
	}

	private static function getPrefix(Listener $listener) : string{
		$p = $listener->ssl ? "https://" : "http://" . $listener->address;
		if(($listener->ssl and $listener->port != 443) or (!$listener->ssl and $listener->port != 80)){
			return $p . ":" . $listener->port;
		}
		return $p;
	}
}
