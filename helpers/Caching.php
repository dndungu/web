<?php

namespace helpers;

class Caching {
	
	protected $sandbox = NULL;
	
	public function __construct() {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('portlet.authenticated', 'checkCache', $this);
	}
	
	public function checkCache($data){
		if(!property_exists($data['portlet']->attributes(), 'cache')) {
			$this->sandbox->fire('cache.failed', $data);
			return;
		}
		$cacheFile = $this->cacheFileName($data);
		$cacheLife = $data['portlet']->attributes()->cache;
		if(file_exists($cacheFile) && (time() - $cacheLife) < filemtime($cacheFile)){
			$data['cache'] = file_get_contents($cacheFile);
			$this->sandbox->fire('cache.exists', $data);
		} else {
			$this->sandbox->fire('cache.missing', $data);
		}
	}
	
	protected function cacheFileName($data){
		$module = (string) $data['portlet']->attributes()->module;
		$controller = (string) $data['portlet']->attributes()->controller;
		$login = $data['user']->getLogin();
		$URI = $data['URI'];
		$hash = md5("$URI.$resource.$login.$module.$controller");
		return "../cache/$hash.json";
	}
	
	public function setCache($data){
		if(!property_exists($data['portlet']->attributes(), 'cache')) return;
		$cacheFile = $this->cacheFileName($data);
		file_put_contents($cacheFile, json_encode($data['response']));
	}
	
}

?>