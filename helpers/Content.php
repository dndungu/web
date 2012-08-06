<?php

namespace helpers;

class Content {
	
	protected $sandbox = NULL;
	
	protected $user = NULL;
	
	protected $base = NULL;
		
	protected $definition = NULL;
		
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->user = $this->sandbox->getHelper('user');
	}
	
	public function setBase($base){
		$this->base = $base;
		$request = explode('/', $this->sandbox->getMeta('URI'));
		$filename = $this->base . '/flows/' . ($request[(count($request) - 1)]) . '.xml';
		try {
			$this->setDefinition('flow', $filename);
		}catch(HelperException $e){
			throw new HelperException($e->getMessage());
		}
	}
		
	public function setDefinition($name, $filename){
		try {
			$this->definition[$name] = $this->loadDefinition($filename);
		}catch(HelperException $e){
			throw new HelperException($e->getMessage());
		}
	}
	
	public function getDefinition($name){
		return array_key_exists($name, $this->definition) ? $this->definition[$name] : NULL;
	}
	
	private function loadDefinition($filename){
		if(is_readable($filename)){
			$definition = simplexml_load_file($filename);
			if($definition){
				return $definition;
			}else{
				throw new HelperException("$filename is not a valid XML definition");
			}
		}else{
			throw new HelperException("$filename is not readable");
		}
	}
	
	public function isInsertable(){
		foreach($this->definition->insert as $insert){
			if($this->attestPermissions((string) $insert->attributes()->access)){
				$filename = $this->base . '/forms/' . ((string) $insert->attributes()->form) . '.xml';
				try{
					$this->setDefinition('insert', $filename);
				}catch(HelperException $e){
					throw new HelperException($e->getMessage());
				}
				return true;
			}
		}
		return false;
	}	
	
	public function isSelectable(){
		foreach($this->definition->select as $select){
			if($this->attestPermissions((string) $select->attributes()->access)) {
				$filename = $this->base . '/grids/' . ((string) $select->attributes()->grid) . '.xml';
				try{
					$this->setDefinition('select', $filename);
				}catch(HelperException $e){
					throw new HelperException($e->getMessage());
				}				
				return true;
			}
		}
		return false;		
	}
	
	public function isUpdateable(){
		foreach($this->definition->update as $update){
			if($this->attestPermissions((string) $update->attributes()->access)) {
				$filename = $this->base . '/forms/' . ((string) $update->attributes()->form) . '.xml';
				try {
					$this->setDefinition('update', $filename);
				}catch(HelperException $e){
					throw new HelperException($e->getMessage());
				}
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