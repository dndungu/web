<?php

namespace base;

class Response {
	
	static function sendHeader($code){
		ob_clean();
		switch($code){
			case 204:
				header("HTTP/1.1 204 No Content");
				break;
			case 400:
				header("HTTP/1.1 400 Bad request");
				break;
			case 402:
				header("HTTP/1.1 403 Payment Required");
				break;
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.1 404 Not Found");
				break;
			case 500:
				header("HTTP/1.1 500 Internal Server Error");
				break;
		}
	}
	
}

?>