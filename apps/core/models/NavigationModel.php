<?php

namespace apps\core;

class NavigationModel {
	
	private $sandbox = NULL;
	
	private $links = array();
	
	private $menu = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->queryDatabase();
	}
	
	private function queryDatabase(){
		$query = sprintf("SELECT `uri`, `label`, `group`, `parent`, `weight` FROM `navigation`");
		$links = $this->sandbox->getLocalStorage()->query($query);
		if(is_null($links)) return;
		$this->addLinks($links);
	}
	
	public function addLinks($links){
		foreach($links as $link){
			$this->links[] = $link;
		}
	}
	
	public function getMenu(){
		$this->menu = array();
		foreach($this->links as $link){
			$this->menu[$link['group']][$link['parent']][] = array('uri' =>$link['uri'], 'label' => $link['label'], 'weight' => $link['weight'], 'class' => $link['class'], 'id' => $link['id']);
		}
		return $this->menu;
	}
	
}