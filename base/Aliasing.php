<?php

namespace base;

class Aliasing {

	protected $sandbox = NULL;

	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('request.passed', 'init', $this);
	}

	public function init($data) {
		try {
			$this->initPortal();
			$this->sandbox->fire('aliasing.passed');
		} catch (BaseException $e) {
			return $this->sandbox->fire('aliasing.failed', ($e->getMessage()));
		}
	}
	
	protected function initPortal(){
		$site = $this->sandbox->getMeta('site');
		$base = $this->sandbox->getMeta('base');
		$homeSetting = $site['home'];
		$sourceSetting = $site['source'];
		$source = "$base/sites/$homeSetting/$sourceSetting";
		if(!is_readable($source)){
			throw new BaseException("Alias package '$source' file is not readable or does not exist.");
		}
		$package = simplexml_load_file($source);
		$this->sandbox->setMeta('package', $package);
		$portal = $this->matchPortal($package);
		if(is_null($portal)) {
			throw new BaseException("Portal does not exists for URI : ".$this->sandbox->getMeta('URI'));
		} else {
			$this->sandbox->setMeta('portal', $portal);
		}
	}

	protected function matchPortal($package){
		$request = $this->sandbox->getMeta('URI');
		foreach($package->portal as $portal){
			foreach($portal->navigation as $match){
				$resource = (string) $match->attributes()->uri;
				if($request === $resource) {
					return $portal;
				}
				$lastkey = strlen($resource) - 1;
				if($resource[$lastkey] === "*") {
					if(substr_count($request, rtrim($resource, "*")) > 0){
						return $portal;
					}
				}
			}
		}
		return NULL;
	}

	public function getAlias(){
		return strtolower($_SERVER['HTTP_HOST']);
	}
		
}

?>