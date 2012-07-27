<?php

namespace apps\core;

class Launch extends \apps\Application {
	
	public function doPost(){
		$base = $this->sandbox->getMeta('base');
		require_once("$base/apps/core/models/Launcher.php");
		$launcher = new Launcher($this->sandbox);
		try{
			$launcher->validate();
			$launcher->createUser();
			$launcher->createSite();
			$launcher->createSiteDatabase();
			$launcher->createAlias();
			$launcher->createContact();
			$launcher->setUserSite();
			$launcher->createSettings();
			$site = $this->sandbox->getMeta('site');
			$alias = $this->sandbox->getHelper('input')->postString('alias_title');
			$destination = $alias.'.'.$site['title'];
			return array('launch' => $destination);
		}catch(\apps\ApplicationException $e){
			$message['post'] = $_POST;
			$message['controller'] = 'launcher.Launch';
			$message['error'] = $e->getMessage();
			$log = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			$this->sandbox->fire('application.error', $log);
			return array('error' => $e->getMessage());
		}
	}
	
}