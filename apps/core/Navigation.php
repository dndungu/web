<?php

namespace apps\core;

class Navigation extends \apps\Application {
	
	public function doGet(){
		$menu = $this->doMenu();
		return $menu;
	}
	
	public function doPost(){
		$menu = $this->doMenu();
		return $menu;
	}
	
	private function doMenu(){
		$base = $this->sandbox->getMeta('base');
		require_once("$base/apps/core/models/NavigationModel.php");
		$model = new NavigationModel($this->sandbox);
		$model->addLinks($this->sandbox->getMeta('navigation'));
		return $model->getMenu();
	}
	
}