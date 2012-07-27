<?php

namespace helpers;

class Input {
			
	public function postEmail($key){
		return filter_var(filter_input(INPUT_POST, $key, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
	}
	
	public function getEmail($key){
		return filter_var(filter_input(INPUT_GET, $key, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
	}
		
	public function postFloat($key){
		return filter_var(filter_input(INPUT_POST, $key, FILTER_SANITIZE_NUMBER_FLOAT), FILTER_VALIDATE_FLOAT);
	}

	public function getFloat($key){
		return filter_var(filter_input(INPUT_GET, $key, FILTER_SANITIZE_NUMBER_FLOAT), FILTER_VALIDATE_FLOAT);
	}
	
	public function postInteger($key){
		return filter_var(filter_input(INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT), FILTER_VALIDATE_INT);
	}

	public function getInteger($key){
		return filter_var(filter_input(INPUT_GET, $key, FILTER_SANITIZE_NUMBER_INT), FILTER_VALIDATE_INT);
	}
	
	public function postBoolean($key){
		return filter_input(INPUT_POST, $key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}

	public function getBoolean($key){
		return filter_input(INPUT_GET, $key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}
	
	public function postRegExp($key, $expression){
		return filter_input(INPUT_POST, $key, FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>$expression)));
	}

	public function getRegExp($key, $expression){
		return filter_input(INPUT_GET, $key, FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>$expression)));
	}
	
	public function postIP($key){
		return filter_input(INPUT_POST, $key, FILTER_VALIDATE_IP);
	}

	public function getIP($key){
		return filter_input(INPUT_GET, $key, FILTER_VALIDATE_IP);
	}
	
	public function postURL($key){
		return filter_input(INPUT_POST, $key, FILTER_VALIDATE_URL);
	}

	public function getURL($key){
		return filter_input(INPUT_GET, $key, FILTER_VALIDATE_URL);
	}
	
	public function postString($key){
		return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
	}
	
	public function getString($key){
		return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
	}
		
	public function postPassword($key){
		$password = Input::postString($key);
		if($password){
			return (strlen($password) < 6 || strlen($password) > 32) ? false : $password;
		}else{
			return false;
		}
	}

	public function getPassword($key){
		$password = $this->getString($key);
		if($password){
			return (strlen($password) < 6 || strlen($password) > 32) ? false : $password;
		} else {
			return false;
		}
	}
	
	public function postGender($key){
		$gender = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
		return $gender == "M" || $gender == "F" ? $gender : false;
	}
	
}