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
					$result = $form->updateRecord();;
					return $result;
					break;
				case "select":
					return $form->selectRecord();
					break;
				case "delete":
					return $form->deleteRecord();
					break;
				case "approve":
					$name = $this->getFormName();
					switch($name){
						case "approvalOne":
							$amount = 'credit';
							break;
						case "approvalTwo":
							$amount = 'approvalOneAmount';
							break;
					}
					$result = $this->sandbox->getLocalStorage()->query(sprintf("UPDATE `apiOrder` SET `%s` = 'Approved', `%sUser` = %d, `%sTime` = %d, `%sAmount` = `%s` WHERE `ID` IN (%s)", $name, $name, $this->sandbox->getHelper('user')->getID(), $name, time(), $name, $amount, $_POST['ids']));
					return json_encode($result);
					break;
				case "reject":
					$name = $this->getFormName();
					switch($name){
						case "approvalOne":
							$amount = 'credit';
							break;
						case "approvalTwo":
							$amount = 'approvalOneAmount';
							break;
					}					
					$result = $this->sandbox->getLocalStorage()->query(sprintf("UPDATE `apiOrder` SET `%s` = 'Rejected', `%sUser` = %d, `%sTime` = %d, `%sAmount` = `%s` WHERE `ID` IN (%s)", $name, $name, $this->sandbox->getHelper('user')->getID(), $name, time(), $name, $amount, $_POST['ids']));
					return json_encode($result);
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
			$form->setSource("$base/apps/content/forms/".$this->getFormName().".xml");
			return $form;
		}catch(\helpers\HelperException $e){
			$this->doCrash($e);
		}
	}
	
	private function getFormName(){
		$base = $this->sandbox->getMeta('base');
		$request = explode('/', $this->sandbox->getMeta('URI'));
		$key = count($request) - 1;
		return $request[$key];		
	}
	
}