<?php

namespace helpers;

class Sandbox {
	
	protected $meta = NULL;
				
	protected $events = NULL;
	
	protected $helpers = array();
	
	protected $globalStorage = NULL;
	
	protected $parentStorage = NULL;
	
	protected $localStorage = NULL;
		
	public function __construct(&$storage) {
		$this->setBase();
		$this->setURI();
		$this->setMethod();
		$this->setGlobalStorage($storage);
		$this->initHelper('Input');
		$this->initHelper('Session');
		$this->initHelper('Site');
		$this->getHelper("site")->autoSetup();
		$this->initHelper('User');
		$this->initHelper('Grid');
		$this->initHelper('Form');
		$this->initHelper('Cell');
	}
	
	public function listen($types = NULL, $method = NULL, $instance = NULL){
		if(is_string($types)) {
			$types = array($types);
		}
		foreach($types as $type){
			$this->events[$type][] = array('instance' => $instance, 'method' => $method);
		}
		return NULL;
	}
	public function fire($type = NULL, &$data = NULL){
		error_log($type);
		if(is_null($type) || !array_key_exists($type, $this->events)) return;
		$listeners = $this->events[$type];
		$parameter = is_array($data) ? ($data) : array(&$data);
		$results = NULL;
		foreach($listeners as $listener){
			$callback = is_null($listener['instance']) ? $listener['method'] : array($listener['instance'], $listener['method']);
			$results[] = call_user_func_array($callback, array(&$data));
		}
		return $results;
	}
						
	protected function setBase(){
		$this->meta['base'] = str_replace('/html', '', getcwd());
	}
		
	protected function setURI(){
		$resource = rtrim($_SERVER['REQUEST_URI'], "/");
		$this->meta['URI'] = strlen($resource) == 0 ? "/" : $resource;
	}
		
	protected function setMethod(){
		$this->meta['method'] = $_SERVER['REQUEST_METHOD'] == "GET" ? "doGet" : "doPost";
	}
		
	public function setMeta($key, &$value){
		$this->meta[$key] = $value;
	}
	
	public function getMeta($key){
		return array_key_exists($key, $this->meta) ? $this->meta[$key] : NULL;
	}
	
	public function initHelper($helper){
		$key = strtolower($helper);
		if(array_key_exists($key, $this->helpers)) {
			return $this->helpers[$key];
		} else {
			try {
				$class = "\helpers\\$helper";
				$base = $this->getMeta('base');
				require_once("$base/helpers/$helper.php");
				$this->helpers[$key] = new $class($this);
				return $this->helpers[$key];
			}catch(Exception $e){
				$this->sandbox->fire('sandbox.error', $e->getMessage());
			}
		}
	}
	
	public function getHelper($helper){
		return $this->helpers[$helper];
	}
	
	public function setGlobalStorage(&$storage){
		$this->globalStorage = &$storage;
	}
	
	public function getGlobalStorage(){
		return $this->globalStorage;
	}
	
	public function setParentStorage(&$storage){
		$this->parentStorage = &$storage;
	}
	
	public function getParentStorage(){
		return $this->parentStorage;
	}
	
	public function setLocalStorage(&$storage){
		$this->localStorage = &$storage;
	}
	
	public function getLocalStorage(){
		return $this->localStorage;
	}
	
}

?>