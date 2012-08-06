<?php

namespace helpers;

class Flow {
	
	private $sandbox = NULL;
	
	private $definition = NULL;
	
	private $user = NULL;
	
	private $updateForm = NULL;
		
	private $insertForm = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->user = $this->sandbox->getHelper('user');
	}
	
	public function getUpdateForm(){
		return (string) $this->updateForm;
	}
	
	public function getInsertForm(){
		return (string) $this->insertForm;
	}
		
	public function setSource($filename){
		if(!is_readable($filename)) {
			throw new HelperException("'$filename' is not readable");
		}
		$this->definition = simplexml_load_file($filename);
		if(!$this->definition) {
			throw new HelperException("'$filename' is not a valid XML table definition");
		}
	}
	
	public function isSelectable(){
		return $this->attestPermissions((string) $this->definition->select->attributes()->access);
	}	
	
	public function isInsertable(){
		foreach($this->definition->insert as $insert){
			if($this->attestPermissions((string) $insert->attributes()->access)){
				$this->insertForm = (string) $insert->attributes()->form;
				return true;
			}
		}
		return false;
	}
	
	public function isUpdateable(){
		foreach($this->definition->update as $update){
			if($this->attestPermissions((string) $update->attributes()->access)) {
				$this->updateForm = (string) $update->attributes()->form;
				return true;
			}
		}
		return false;
	}
	
	public function isDeleteable(){
		return $this->attestPermissions((string) $this->definition->delete->attributes()->access);
	}
	
	public function attestPermissions($permission){
		return in_array($permission, $this->user->getPermissions());
	}	
	
}