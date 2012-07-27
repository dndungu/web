<?php

namespace apps\core;

class Launcher {
	
	private $sandbox = NULL;
	
	private $site = NULL;
	
	private $user = NULL;
	
	private $contact = NULL;
	
	private $alias = NULL;
	
	private $setting = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->site = new \helpers\Site($this->sandbox);
	}
	
	public function aliasExists(){
		try {
			$alias = $this->sandbox->getHelper('input')->postString('alias_title');
			$alias = strtolower(trim($alias));
			if($alias == "www" || strlen($alias) < 3) return "Yes";
			$alias = $alias.'.'.$this->sandbox->getHelper('site')->getAlias();
			$alias = $this->sandbox->getGlobalStorage()->sanitize($alias);
			$query = sprintf("SELECT COUNT(*) AS count FROM `alias` WHERE `title` = '%s'", $alias);
			$rows = $this->sandbox->getGlobalStorage()->query($query);
			return (boolean) $rows[0]['count'] ? "Yes" : "No";
		}catch(\helpers\HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function createUser(){
		try {
			$insert['table'] = 'user';
			$insert['content']['creationTime'] = time();
			$insert['content']['email'] = $this->user['email'];
			$insert['content']['login'] = $this->user['email'];
			$insert['content']['password'] = md5($this->user['password']);
			$userID = $this->sandbox->getGlobalStorage()->insert($insert);
			$this->site->setUser($userID);
		}catch(\helpers\HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function createContact(){
		try{
			$insert['content'] = $this->contact;
			$insert['content']['user'] = $this->site->getUser();
			$insert['content']['site'] = $this->site->getID();
			$insert['content']['creationTime'] = time();
			$insert['table'] = 'contact';
			$this->sandbox->getLocalStorage()->insert($insert);
		}catch(\helpers\HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function setUserSite(){
		try {
			$update['table'] = 'user';
			$update['content'] = array('site' => $this->site->getID());
			$update['constraints'] = array('ID' => $this->site->getUser());
			$this->sandbox->getGlobalStorage()->update($update);
			$roles = $this->sandbox->getGlobalStorage()->query("SELECT `ID` FROM `role` WHERE `title` = 'owner' LIMIT 1");
			if(is_null($roles)) return;
			$assignment = sprintf("INSERT INTO `rolemap` (`site`, `user`, `role`, `creationTime`) VALUES(%d, %d, %d, %d)", $this->site->getID(), $this->site->getUser(), $roles[0]['ID'], time());
			$this->sandbox->getGlobalStorage()->query($assignment);
		}catch(\helpers\HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function createSite(){
		try {
			$siteSetting = $this->sandbox->getMeta('site');
			$site['user'] = $this->site->getUser();
			$site['home'] = $siteSetting['home'];
			$site['source'] = 'client.xml';
			$site['parent'] = $siteSetting['site'];
			$site['creationTime'] = time();
			$insert['table'] = 'site';
			$insert['content'] = $site;
			$siteID = $this->sandbox->getGlobalStorage()->insert($insert);
			$this->site->setID($siteID);
		}catch(HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function createSiteDatabase(){
		try {
			$db = $this->generateDatabaseName();
			$settings = $this->sandbox->getMeta('settings');
			$queryCreate = sprintf("CREATE DATABASE IF NOT EXISTS `%s`", $db);
			$this->sandbox->getLocalStorage()->query($queryCreate);
			$queryGrant = sprintf("GRANT ALL PRIVILEGES ON  `%s` . * TO  '%s'@'localhost'", $db, $settings['user']);
			$this->sandbox->getLocalStorage()->query($queryGrant);
			$parent = $this->sandbox->getMeta('site');
			
			$settings['schema'] = $db;
			$storage = new \helpers\Storage($settings);
			$sql = file_get_contents($this->sandbox->getMeta('base')."/sites/".$parent['home']."/client.sql");
			$storage->multi_query($sql);
			
		}catch(\helpers\HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	private function generateDatabaseName(){
		$alias = $this->site->getAlias();
		$parent = $this->sandbox->getMeta('site');
		return $parent['home'].'_'.$alias;
	}
		
	public function createSettings(){
		try {
			
			$inheritance = $this->sandbox->getMeta('settings');
			foreach($inheritance as $key => $value){
				$insert['table'] = 'setting';
				$insert['content']['key'] = $key;
				if($key == "schema"){
					$insert['content']['value'] = $this->generateDatabaseName();
				}else{
					$insert['content']['value'] = $value;
				}
				$insert['content']['site'] = $this->site->getID();
				$insert['content']['creationTime'] = time();
				$inserts[] = $insert;
			}		
			
			foreach($this->setting as $key => $value){
				$insert['table'] = 'setting';
				$insert['content']['key'] = $key;
				$insert['content']['value'] = $value;
				$insert['content']['site'] = $this->site->getID();
				$insert['content']['creationTime'] = time();
				$inserts[] = $insert;
			}
			
			foreach($inserts as $insert){
				$results[] = $this->sandbox->getGlobalStorage()->insert($insert);
			}
			
			return $results;
			
		}catch(HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function createAlias(){
		try {
			$alias['site'] = $this->site->getID();
			$site = $this->sandbox->getMeta('site');
			$domain = $this->site->getAlias().'.'.$site['title'];
			$alias['title'] = $domain;
			$alias['creationTime'] = time();
			$insert['table'] = 'alias';
			$insert['content'] = $alias;
			$insert['content']['creationTime'] = time();
			return $this->sandbox->getGlobalStorage()->insert($insert);
		}catch(HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function validate(){
		$translator = $this->sandbox->getHelper('translation');
		$input = $this->sandbox->getHelper('input');

		$alias = $input->postString('alias_title');
		if($this->aliasExists() == "Yes") throw new \apps\ApplicationException("Can not allow duplicate alias '$alias'");
		if(strlen($alias)<3) throw new \apps\ApplicationException("Alias is too short");
		$this->site->setAlias($alias);
		
		$this->user['email'] = $input->postEmail('user_email');
		$this->user['password'] = $input->postPassword('user_password');
		foreach ($this->user as $key => $value) {
			if(!$value) throw new \apps\ApplicationException($translator->translate("validator_$key"));
		}
	
		$this->setting['category'] = $input->postString('setting_category');
		$this->setting['title'] = $input->postString('setting_title');
	
		$this->contact['firstname'] = $input->postString('contact_firstname');
		$this->contact['lastname'] = $input->postString('contact_lastname');
		$this->contact['country'] = $input->postInteger('contact_country');
		$this->contact['phone'] = $input->postString('contact_phone');
		$this->contact['DOB'] = $input->postInteger('contact_dob');
		$this->contact['gender'] = $input->postGender('contact_gender');
		foreach ($this->contact as $key => $value) {
			if(!$value) throw new \apps\ApplicationException($translator->translate("validator_$key"));
		}
	
	}
	
}