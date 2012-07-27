<?php

namespace base;

class Controller {
	
	protected $sandbox = NULL;
	
	protected $handlers = NULL;
		
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->initListeners();
		$this->initHandlers();
		$this->sandbox->fire('request.passed');
	}
	
	protected function initListeners(){
		$this->sandbox->listen('aliasing.failed', 'sendHeader404', $this);
		$this->sandbox->listen('authentication.failed', 'sendDisallowed', $this);
		$this->sandbox->listen('routing.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.passed', 'sendContent', $this);
	}
	
	protected function initHandlers(){
		$this->initHandler('Logging');
		$this->initHandler('Aliasing');
		$this->initHandler('Authentication');
		$this->initHandler('Routing');
		$this->initHandler('Assembly');
	}
	
	protected function initHandler($handler){
		try {
			$base = $this->sandbox->getMeta('base');
			$class = "\base\\$handler";
			require_once("$base/base/$handler.php");
			$this->handlers[strtolower($handler)] = new $class($this->sandbox);
		}catch(Exception $e){
			$message = $e->getMessage();
			error_log($message);
			$this->sandbox->fire('sandbox.error', $message);
		}
		
	}	
	
	public function sendDisallowed(){
		$type = (string) $this->sandbox->getMeta('portal')->attributes()->type;
		if($type == "html"){
			$URI = $this->sandbox->getMeta('URI');
			if($URI === "/signin") {
				$address = "/";
			}else{
				$address = "/signin";
			}
			$this->sandbox->getHelper('session')->write('destination', $URI);
			header("Location: $address");
			exit;
		} else {
			$this->sendHeader403();
		}
	}
			
	public function sendContent(&$content){
		print $content;
	}
	
	public function sendHeader403(){
		return Response::sendHeader(403);
	}
	
	public function sendHeader404(){
		return Response::sendHeader(404);
	}

	public function sendHeader500(){
		return Response::sendHeader(500);
	}
		
	public function log($latency){
		return $this->sandbox->fire('latency.log', $latency);
	}
	
}

?>