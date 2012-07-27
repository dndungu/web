<?php

namespace helpers;

class Emailer {
	
	private $sandbox = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
	}
	
	public function send($to, $subject, $message){
		$headers = $this->createHeaders();
		try {
			$sent = mail($to, $subject, $message, $headers);
			return $sent;
		}catch(\Exception $e){
			throw new HelperException($e->getMessage());
		}
	}
	
	private function createHeaders(){
		$settings = $this->sandbox->getMeta('settings');
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "From: ".$settings['emailer_from']."\r\n";
		return $headers;
	}
	
}