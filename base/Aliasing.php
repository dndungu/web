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
			$this->initSite();
			$this->initSettings();
			$this->initTranslation();
			$this->initLocalStorage();
			$this->initParentStorage();
			$this->initPortal();
			$this->sandbox->fire('aliasing.passed');
		} catch (BaseException $e) {
			$message = $e->getMessage();
			return $this->sandbox->fire('aliasing.failed', $message);
		}
	}

	protected function initTranslation(){
		try {
			$this->sandbox->initHelper('Translation');
		}catch(\helpers\HelperException $e){
			throw new BaseException($e->getMessage());
		}
	}
	
	protected function initSite(){
		$sql = sprintf("SELECT `site`, `parent`, `home`, `source`, `title` FROM `alias` LEFT JOIN `site` ON `alias`.`site` = `site`.`ID` WHERE `title` = '%s' LIMIT 1", $this->getAlias());
		$rows = $this->sandbox->getGlobalStorage()->query($sql);
		if(is_null($rows)) throw new BaseException('No site found');
		$this->sandbox->setMeta('site', $rows[0]);
	}

	protected function initSettings(){
		$site = $this->sandbox->getMeta('site');
		$sql = sprintf("SELECT * FROM `setting` WHERE `site` = %d", $site['site']);
		$settings = $this->sandbox->getGlobalStorage()->query($sql);
		if(is_null($settings)) throw new BaseException('No settings found');
		$result = NULL;
		foreach($settings as $setting){
			$key = $setting['key'];
			$result[$key] = $setting['value'];
		}
		$this->sandbox->setMeta('settings', $result);
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
		$handler = NULL;
		foreach($package->portal as $portal){
			foreach($portal->navigation as $match){
				$resource = (string) $match->attributes()->uri;
				if($request === $resource) {
					return $portal;
				}
				$lastkey = strlen($resource) - 1;
				if($resource[$lastkey] === "*") {
					if(substr_count($request, rtrim($resource, "*")) > 0){
						$handler = $portal;
					}
				}
			}
		}
		return $handler;
	}

	public function getAlias(){
		return strtolower($_SERVER['HTTP_HOST']);
	}
	
	protected function initLocalStorage(){
		$settings = $this->sandbox->getMeta('settings');
		$storage = new \helpers\Storage($settings);
		$this->sandbox->setLocalStorage($storage);
	}

	public function initParentStorage(){
		$parent = $this->sandbox->getHelper('site')->getParent();
		$rows = $this->sandbox->getGlobalStorage()->query(sprintf("SELECT * FROM `setting` WHERE `site` = %d", $parent));
		if(!$rows) return;
		foreach($rows as $row){
			$setting[$row['key']] = $row['value'];
		}
		$storage = new \helpers\Storage($setting);
		$this->sandbox->setParentStorage($storage);
	}
	
}

?>