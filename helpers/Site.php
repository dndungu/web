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
	}
	
	private function findSite(){
		$query = sprintf("SELECT `site`, `parent`, `home` FROM `alias` LEFT JOIN `site` ON `alias`.`site` = `site`.`ID` WHERE `alias`.`title` = '%s' LIMIT 1", $this->getAlias());
		$sites = $this->sandbox->getGlobalStorage()->query($query);
		if(is_null($sites)) {
			return NULL;
		} else {
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