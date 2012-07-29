<?php

namespace helpers;

class Site {
	
	private $sandbox = NULL;
	
	private $ID = NULL;
	
	private $parent = NULL;
	
	private $home = NULL;
	
	private $alias = NULL;
	
	private $settings = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
	}
	
	public function autoSetup(){
		$this->setAlias(strtolower($_SERVER['HTTP_HOST']));
		$site = $this->findSite();
		$this->setID($site['site']);
		$this->sandbox->getHelper('session')->write('site', $site['site']);
		$this->setParent($site['parent']);
		$this->setHome($site['home']);
		$settings = $this->findSettings();
		$this->setSettings($settings);
		$this->initTranslation();
		$this->initLocalStorage();
		$this->initParentStorage();
	}
	
	protected function initTranslation(){
		try {
			$this->sandbox->initHelper('Translation');
		}catch(\helpers\HelperException $e){
			throw new BaseException($e->getMessage());
		}
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
	
	private function findSite(){
		$query = sprintf("SELECT `site`, `parent`, `home`, `source`, `title` FROM `alias` LEFT JOIN `site` ON `alias`.`site` = `site`.`ID` WHERE `alias`.`title` = '%s' LIMIT 1", $this->getAlias());
		$sites = $this->sandbox->getGlobalStorage()->query($query);
		if(is_null($sites)) {
			return NULL;
		} else {
			$this->sandbox->setMeta('site', $sites[0]);
			return $sites[0];
		}
	}
	
	private function findSettings(){
		$query = sprintf("SELECT * FROM `setting` WHERE `site` = %d", $this->getID());
		$rows = $this->sandbox->getGlobalStorage()->query($query);
		if(is_null($rows)){
			return NULL;
		}else{
			$settings = array();
			foreach($rows as $row){
				$settings[$row['key']] = $row['value'];
			}
			$this->sandbox->setMeta('settings', $settings);
			return $settings;
		}
	}
	
	public function getID(){
		return $this->ID;
	}
	
	public function setID($ID){
		$this->ID = $ID;
	}
	
	public function getUser(){
		return $this->user;
	}
	
	public function setUser($user){
		$this->user = $user;
	}
	
	public function getParent(){
		return $this->parent;
	}
	
	public function setParent($parent){
		$this->parent = $parent;
	}
	
	public function getHome(){
		return $this->home;
	}
	
	public function setHome($home){
		$this->home = $home;
	}
	
	public function getAlias(){
		return $this->alias;
	}
	
	public function setAlias($alias){
		$this->alias = $alias;
	}
	
	public function getSettings(){
		return $this->settings;
	}
	
	public function setSettings($settings){
		$this->settings = $settings;
	}
		
}