<?php

namespace apps\content;

class CellStudio extends \apps\Application {
	
	public function doPost() {
		try{
			$base = $this->sandbox->getMeta('base');
			$cell = $this->sandbox->getHelper('cell');
			$request = explode('/', $this->sandbox->getMeta('URI'));
			$key = count($request) - 1;
			$name = $request[$key];
			$cell->setSource("$base/apps/content/forms/$name.xml");
			return $cell->asHTML();
		}catch(HelperException $e){
			$this->doCrash($e);
		}
	}
	
}