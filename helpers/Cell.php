<?php

namespace helpers;

class Cell {
	
	private $definition = NULL;
	
	private $sandbox = NULL;
	
	private $name = NULL;
	
	private $flow = NULL;
	
	private $record = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
	}
	
	public function setSource($filename){
		if(!is_readable($filename)) {
			throw new HelperException("'$filename' is not readable");
		}
		$base = $this->sandbox->getMeta('base');
		$request = explode('/', $this->sandbox->getMeta('URI'));
		$key = count($request) - 1;
		$name = $request[$key];		
		$formfilename = substr_count($name, 'approval') || $name == 'order' ? "$base/apps/content/forms/paymentOrder.xml" : $filename;
		$this->definition = simplexml_load_file($formfilename);
		if(!$this->definition) {
			throw new HelperException("'$filename' is not a valid XML form definition");
		}
		$this->name = (string) $this->definition->attributes()->name;
		if(!strlen($this->name)){
			throw new HelperException('');
		}
		$flowfile = str_replace('/forms/', '/flows/', $filename);
		if(!is_readable($flowfile)) {
			throw new HelperException($flowfile.' is not a valid XML flow definition');
		}else{
			$this->flow = new Flow($this->sandbox);
			$this->flow->setSource($flowfile);
		}
	}
			
	public function asHTML(){
		$this->selectRecord();
		if(!$this->record) throw new HelperException('no record with identifier ' . $this->sandbox->getHelper('input')->postInteger('primarykey'));
		$primarykey = (string) $this->definition->attributes()->primarykey;
		$html[] = '<div class="'.$this->name.' gridCell" title="'.$this->record[0][$primarykey].'">';
		if(property_exists($this->definition, "fieldset")){
			foreach($this->definition->fieldset as $fieldset){
				if(property_exists($fieldset, "field")){
					$html[] = $this->createCells($fieldset);
				}
			}
		}
		if(property_exists($this->definition, "field")){
			$html[] = $this->createCells($this->definition);
		}
		$html[] = $this->createActions();
		$html[] = '</div>';
		return join("\n", $html);
	}
	
	private function createActions(){
		$translator = $this->sandbox->getHelper('translation');
		$html[] = "\t".'<div class="actionsCell">';
		$request = explode('/', $this->sandbox->getMeta('URI'));
		$key = count($request) - 1;
		$name = $request[$key];
		if($this->flow->isUpdateable()){
			$action = substr_count($name, 'approval') ? 'action.process' : 'action.edit';
			$html[] = "\t\t".'<input type="button" name="updater" value="'.$translator->translate($action).'" class="gridPrimaryButton"/>';
		}
		if($this->flow->isDeleteable()){
			$html[] = "\t\t".'<input type="button" name="deleter" value="'.$translator->translate('action.delete').'" class="gridDeleteButton"/>';
		}
		$html[] = "\t".'</div>';
		return implode("\n", $html);
	}
	
	private function createCells(&$node){
		$html = array();
		foreach($node->field as $field){
			if(strlen((string) $field->attributes()->type)){
				$html[] = $this->createCell($field);
			}
		}
		return join("\n", $html);
	}
	
	private function createCell(&$field){
		$join = (string) $field->attributes()->join;
		foreach($field->element as $element){
			$html[] = "\t".'<div class="rowCell">';
			$type = (string) $element->attributes()->type;
			if(in_array($type, array('password'))) continue;
			$name = (string) $element->attributes()->name;
			$style = $this->getStyle($element);
			$html[] = "\t\t".'<div class="titleCell">'.$this->getLabel($element).'</div>';
			switch($type){
				case 'options':
					$this->selectOptions($field, $element);
					if($this->record[0][$join]){
						$html[] = "\t\t".'<div class="contentCell">'.implode(', ', $this->record[0][$join]).'</div>';
					}
					break;
				case 'select':
					$html[] = "\t\t".'<div class="contentCell">'.$this->selectReference($element).'</div>';
					break;
				default:
					$html[] = "\t\t".'<div class="contentCell">'.$this->record[0][$name].'</div>';
					break;
			}
			$html[] = "\t".'</div>';
		}
		return join("\n", $html);
	}
	
	private function selectReference(&$element){
		$value = (string) $element->attributes()->value;
		$name = (string) $element->attributes()->name;
		$display = (string) $element->attributes()->display;
		$select['table'] = (string) $element->attributes()->lookup;
		$select['fields'][] = $display;
		$select['constraints'][$value] = $this->record[0][$name];
		$rows = $this->getStorage()->select($select);
		return $rows ? $rows[0][$display] : "";
	}
	
	private function selectOptions(&$field, &$element){
		$translator = $this->sandbox->getHelper('translation');
		$lookup = (string) $element->attributes()->lookup;
		$value = (string) $element->attributes()->value;
		$label = (string) $element->attributes()->display;
		$filter = (string) $element->attributes()->filter;
		$primarykey = (string) $this->definition->attributes()->primarykey;
		$table = (string) $field->attributes()->join;
		$query[] = sprintf("SELECT `%s` FROM `%s`", $label, $table);
		$query[] = sprintf("LEFT JOIN `%s` ON (`%s`.`%s` = `%s`.`%s`)", $lookup, $table, (string) $element->attributes()->name, $lookup, $value);
		$query[] = sprintf("WHERE `%s` = %d", (string) $this->definition->attributes()->name, $this->record[0][$primarykey]);
		$filterQuery = strlen($filter) ? $this->getFilterQuery($filter) : false;
		if($filterQuery){
			$query[] = $filterQuery;
		}
		$storage = (string) $this->definition->attributes()->storage;
		if($storage != 'local'){
			$query[] = sprintf("AND `%s`.`site` = %d", $table, $this->sandbox->getHelper('site')->getID());
		}
		$query = implode(' ', $query);
		$rows = $this->getStorage()->query($query);
		if(!$rows){
			$this->record[0][$table] = false;
		}else{
			foreach($rows as $row){
				$title = $translator->translate($row[$label]);
				$title = strlen($title) ? $title : $translator->translate($row[$label].'.label');
				$title = strlen($title) ? $title : $row[$label];
				$options[] = $title;
			}
			$this->record[0][$table] = $options;
		}
	}
	
	private function getFilterQuery($filter){
		$filters = json_decode($filter);
		foreach($filters as $key => $value){
			switch(gettype($value)){
				case 'integer':
					$query[] = sprintf("AND `%s` = %d", $key, $value);
					break;
				case 'decimal':
					$query[] = sprintf("AND `%s` = %f", $key, $value);
					break;
				case 'string':
					$query[] = sprintf("AND `%s` = '%s'", $key, $value);
					break;
			}
		}
		return isset($query) ? implode(' ', $query) : false;
	}
	
	private function getLabel(&$node){
		$index = (string) $node->attributes()->label;
		return $this->sandbox->getHelper('translation')->translate($index);
	}
	
	private function getStyle(&$node){
		if(!property_exists($node, 'class')) return;
		foreach($node->class as $class){
			$style[] = (string) $class;
		}
		return implode(' ', $style);
	}
	
	private function selectRecord(){
		if(!$this->flow->isSelectable()) throw new HelperException('data access violation');
		$identifier = $this->sandbox->getHelper('input')->postInteger('primarykey');
		if(!$identifier) throw new HelperException('no identifier provided');
		$key = (string) $this->definition->attributes()->primarykey;
		$select['table'] = $this->name;
		$select['constraints'][$key] = $identifier;
		$this->record = $this->getStorage()->select($select);
		$this->formatRecords();
	}
	
	private function formatRecords(){
		if(!$this->record) return;
		$settings = $this->sandbox->getHelper('site')->getSettings();
		foreach($this->record as $key => $record){
			$recordKeys = array_keys($record);
			$timeKeys = array('creationTime', 'expiryTime', 'scheduleTime');
			foreach($timeKeys as $timeKey){
				if(in_array($timeKey, $recordKeys)){
					$this->record[$key][$timeKey] = date($settings['timeformat'], $record[$timeKey]);
				}
			}
		}
	}	
	
	public function getStorage(){
		$storage = (string) $this->definition->attributes()->storage;
		switch($storage){
			case "global":
				return $this->sandbox->getGlobalStorage();
				break;
			case "parent":
				return $this->sandbox->getParentStorage();
				break;
			case "local":
				return $this->sandbox->getLocalStorage();
				break;
			default:
				return $this->sandbox->getLocalStorage();
				break;
		}
	}	
	
}