<?php

namespace helpers;

class GridQuery {
	
	private $sandbox = NULL;
	
	private $definition = NULL;
		
	private $offset = 0;
	
	private $limit = 20;
	
	private $order = array('column' => NULL, 'direction' => NULL);
	
	private $storage = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
	}
	
	public function setDefinition(&$definition){
		$this->definition = &$definition;
	}
	
	public function setStorage(&$storage){
		$this->storage = &$storage;
	}
	
	public function getStorage(){
		return $this->storage;
	}
	
	public function browseQuery(){
		$staticQuery = (string) $this->definition->records->query;
		if(strlen($staticQuery)) return $staticQuery;
		$query[] = $this->buildFields();
		$query[] = $this->buildFrom();
		$query[] = $this->buildLeftJoins();
		$query[] = $this->buildFilter();
		$query[] = $this->buildOrderBy();
		$query[] = $this->buildLimit();
		return implode(' ', $query);
	}
	
	public function countQuery(){
		$key = (string) $this->definition->columns->attributes()->primarykey;
		$query[] = 'SELECT COUNT(*) `rowCount`';
		$query[] = $this->buildFrom();
		$query[] = $this->buildLeftJoins();
		$query[] = $this->buildFilter();
		return implode(' ', $query);
	}
	
	public function getOffset(){
		$offset = $this->sandbox->getHelper('input')->postInteger('offset');
		$this->offset = $offset ? $offset : $this->offset;
		return $this->offset;
	}
	
	public function getLimit(){
		return $this->limit;
	}
	
	public function getOrderColumn(){
		return $this->order['column'];
	}
	
	public function getOrderDirection(){
		return $this->order['direction'];
	}	
	
	protected function buildFields(){
		$key = (string) $this->definition->columns->attributes()->primarykey;
		$columns[] = (substr_count($key, '.') || substr_count($key, '`') ? $key : "`$key`") . " AS `primarykey`";
		foreach($this->definition->columns->column as $column){
			$field = (string) $column->attributes()->field;
			$name = (string) $column->attributes()->name;
			$columns[] = (substr_count($field, '`') ? "$field" : "`$field`") . " AS `$name`";
		}
		$fields = isset($columns) ? join(", ", $columns) : "*";
		return sprintf("SELECT %s", $fields);
	}

	protected function buildFrom(){
		return sprintf("FROM `%s`", ((string) $this->definition->attributes()->name));
	}
	
	protected function buildFilter(){
		$keywords = $this->sandbox->getHelper('input')->postString('keywords');
		if(strlen($keywords)){
			$parametersCount = intval($this->definition->records->search->attributes()->parameters);
			$parametersQuery = (string) $this->definition->records->search;
			$args[] = "WHERE $parametersQuery";
			while($parametersCount--){
				if(is_numeric($keywords)){
					$args[] = $keywords;
				}else{
					$args[] = "%$keywords%";
				}
			}
			$query[] = call_user_func_array('sprintf', $args);
		}else{
			$query[] = "WHERE 1 = 1";
		}
		if(!property_exists($this->definition->records, 'filter')) return $query[0];
		foreach($this->definition->records->filter as $filter){
			$field= (string) $filter->attributes()->field;
			$value = (string) $filter->attributes()->value;
			$name = substr_count($field, '.') || substr_count($field, '`') ? $field : "`$field`";
			if(in_array($value, array('{user}', '{site}', '{province}', '{district}', '{facility}'))){
				switch($value){
					case "{user}":
						$query[] = sprintf("`user` = %d", $this->sandbox->getHelper('user')->getID());
						break;
					case "{site}":
						$query[] = sprintf("`site` = %d", $this->sandbox->getHelper('site')->getID());
						break;
					case "{province}":
						$query[] = sprintf("`site` = %d", $this->sandbox->getHelper('user')->getProvince());
						break;
					case "{district}":
						$query[] = sprintf("`site` = %d", $this->sandbox->getHelper('user')->getDistrict());
						break;
					case "{facility}":
						$query[] = sprintf("`site` = %d", $this->sandbox->getHelper('user')->getFacility());
						break;						
				}
			}else{
				if(is_numeric($value)){
					$query[] = "$name = $value";
				}else{
					$query[] = "$name = '$value'";
				}
			}
		}
		return implode(' AND ', $query);
	}
	
	protected function buildLeftJoins(){
		foreach($this->definition->records->leftjoin as $leftjoin){
			$join = (string) $leftjoin;
			if(strlen($join)){
				$leftjoins[] = "LEFT JOIN $join";
			}
		}
		return isset($leftjoins) ? join(" ", $leftjoins) : "";
	}
	
	protected function buildOrderBy(){
		$ordercolumn = $this->sandbox->getHelper('input')->postString('ordercolumn');
		$orderdirection = $this->sandbox->getHelper('input')->postString('orderdirection');
		$this->order['column'] = $ordercolumn ? $ordercolumn : (string) $this->definition->records->ordercolumn;
		$this->order['direction'] = $orderdirection ? $orderdirection : (string) $this->definition->records->orderdirection;
		$table = (string) $this->definition->attributes()->name;
		$columns = $this->getStorage()->getColumns($table);
		$orderColumn = substr_count($this->order['column'], '.') || substr_count($this->order['column'], '`') ? $this->order['column'] : '`'.$this->order['column'].'`';
		return sprintf("ORDER BY %s %s", $orderColumn, $this->order['direction']);
	}
	
	protected function buildLimit(){
		$parts = explode("/", $this->sandbox->getMeta('URI'));
		$this->offset = array_key_exists('offset', $_POST) ? intval(trim($_POST['offset'])) : $this->offset;
		$this->limit = array_key_exists('limit', $_POST) ? intval(trim($_POST['limit'])) : $this->limit;
		return sprintf("LIMIT %d, %d", $this->offset, $this->limit);	
	}
	
}