<?php

namespace apps\content;

class GridStudio extends \apps\Application {
	
	public function doGet(){
		try{
			$grid = $this->getGrid();
			return ($grid->asHTML());
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}
	}
	
	public function doPost(){
		try {
			$this->checkCommand();
			$grid = $this->getGrid();
			header('Content-type: application/json');
			return $grid->browseRecords();
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}
	}
	
	private function getGrid(){
		try {
			$base = $this->sandbox->getMeta('base');
			$request = explode('/', $this->sandbox->getMeta('URI'));
			$key = count($request) - 1;
			$name = $request[$key];
			$grid = $this->sandbox->getHelper('grid');
			$grid->setSource("$base/apps/content/grids/$name.xml");
			return $grid;
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}		
	}
	
}