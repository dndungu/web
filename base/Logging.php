<?php

namespace base;

class Logging {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('application.error', 'logError', $this);
		$this->sandbox->listen('latency.log', 'logAccess', $this);
		$this->sandbox->listen('aliasing.failed', 'logError', $this);
		$this->sandbox->listen('authentication.failed', 'logError', $this);
		$this->sandbox->listen('routing.failed', 'logError', $this);
		$this->sandbox->listen('assembly.failed', 'logError', $this);
	}
	
	public function logAccess($latency){
		$log = $this->logMeta();
		$log['latency'] = $latency;
		$insert = array('table' => 'access', 'content' => $log);
		try{
			$this->sandbox->getGlobalStorage()->insert($insert);
		}catch(\helpers\HelperException $e){
			error_log($e->getMessage());
		}
	}
	
	public function logError($data){
		print_r($data);exit;
		$log = $this->logMeta();
		$log['message'] = $data;
		$log['trace'] = json_encode(debug_backtrace());
		$insert = array('table' => 'error', 'content' => $log);
		try{
			$this->sandbox->getGlobalStorage()->insert($insert);
		}catch(\helpers\HelperException $e){
			error_log($e->getMessage());
		}		
	}
	
	protected function logMeta() {
		$user = $this->sandbox->getHelper('user')->getUser();
		if($user['isGuest'] == 'Yes'){
			$log['guest'] = $user['ID'];
		} else {
			$log['user'] = $user['ID'];
		}
		$log['resource'] = $this->sandbox->getMeta('URI');
		$log['IP'] = $_SERVER['REMOTE_ADDR'];
		$log['creationTime'] = microtime(true);
		return $log;
	}
	
}

?>