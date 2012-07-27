<?php

namespace apps\content;

class FormStudio extends \apps\Application {
	
	public function doGet(){
		try {
			$form = $this->getForm();
			return ($form->asHTML());
		}catch(\apps\ApplicationException $e){
			$this->doCrash($e);
		}
	}
	
	public function doPost(){
		try {
			$this->checkCommand();
			$form = $this->getForm();
			switch(trim($_POST['command'])){
				case "insert":
					return $form->createRecord();
					break;
				case "update":
					return $form->updateRecord();
					break;
				case "select":
					return $form->selectRecord();
					break;
				case "delete":
					return $form->deleteRecord();
					break;
			}
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}
	}
	
	private function getForm(){
		try{
			$base = $this->sandbox->getMeta('base');
			$form = $this->sandbox->getHelper('form');
			$request = explode('/', $this->sandbox->getMeta('URI'));
			$key = count($request) - 1;
			$name = $request[$key];
			$form->setSource("$base/apps/content/forms/$name.xml");
			return $form;
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}
	}
	
}