<?php

namespace apps;

require_once("ApplicationException.php");

class Application {
	
	protected $sandbox = NULL;
	
	protected $title = NULL;
	
	protected $body = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
	}
	
	public function doGet(){
		
	}
	
	public function doPost(){
		
	}
	
	public function doRedirect(){
		$destination = $this->sandbox->getHelper('session')->read('destination');
		$destination = is_null($destination) ? "/" : $destination;
		header("Location: $destination");
		exit;
	}
		
	public function doCrash($e){
		$this->doLog($e);
		\base\Response::sendHeader(400);
	}
	
	public function doLog($e){
		$message = $e->getMessage();
		$this->sandbox->fire('application.error', $message);
	}

	public function initModel($app, $model){
		$base = $this->sandbox->getMeta('base');
		require_once("$base/apps/$app/models/$model.php");
		$class = "apps\\$app\\$model";
		return new $class($this->sandbox);
	}
	
	public function checkCommand(){
		if(!array_key_exists('command', $_POST)){
			throw new \helpers\HelperException('missing command in post parameters');
		}
	}
}

?>