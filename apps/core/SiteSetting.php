<?php

namespace apps\core;

class SiteSetting extends \apps\Application {
	public function doGet(){
		return $this->doPage();
	}
	public function doPost(){
		return $this->doPage();
	}
	private function doPage(){
		$settings = $this->sandbox->getMeta('settings');
		$page['uri'] = $this->sandbox->getMeta('URI');
		$page['title'] = $settings['title'];
		$page['locale'] = $this->sandbox->getHelper('translation')->getLocale();
		return $page;
	}
}