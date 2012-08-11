<?php

namespace apps\api;

use base\Response;

use apps\ApplicationException;

class JsonStudio extends \apps\Application {
		
	public function doGet(){
		//$this->doShield();
		switch($this->sandbox->getMeta('URI')){
			case "/api/v1/budgets":
				echo json_encode($this->sandbox->getLocalStorage()->select(array('table' => 'budget', 'fields' => array('ID', 'title', 'notes'))), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
			case "/api/v1/payment_categories":
				echo json_encode($this->sandbox->getLocalStorage()->select(array('table' => 'orderType', 'fields' => array('ID', 'title'))), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
			case "/api/v1/recipient_categories":
				echo json_encode($this->sandbox->getLocalStorage()->select(array('table' => 'beneficiaryType', 'fields' => array('ID', 'title'))), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
			case "/api/v1/beneficiaries":
				echo json_encode($this->sandbox->getLocalStorage()->query("SELECT `firstname`, `middlename`, `lastname`, `beneficiaryType`, `MSISDN`, `district`.`title` AS `district`, `county`.`title` AS `county`, `province`.`title` AS `province`, `facility`.`title` AS `facility`, false AS `sent` FROM `beneficiary` LEFT JOIN `district` ON (`beneficiary`.`district` = `district`.`ID`) LEFT JOIN `county` ON (`beneficiary`.`county` = `county`.`ID`) LEFT JOIN `province` ON (`beneficiary`.`province` = `province`.`ID`) LEFT JOIN `facility` ON (`beneficiary`.`facility` = `facility`.`ID`)"), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
		}
	}
	
	public function doPost(){
		//$this->doShield();
		switch($this->sandbox->getMeta('URI')){
			case "/api/v1/payment":
				$record['firstname'] = $this->sandbox->getHelper('input')->postString('recipient_firstname');
				$record['middlename'] = $this->sandbox->getHelper('input')->postString('recipient_middlename');
				$record['lastname'] = $this->sandbox->getHelper('input')->postString('recipient_lastname');
				$record['beneficiaryType'] = $this->sandbox->getHelper('input')->postInteger('recipient_category');
				$record['orderType'] = $this->sandbox->getHelper('input')->postInteger('payment_category');
				$record['credit'] = $this->sandbox->getHelper('input')->postString('amount');
				$record['budget'] = $this->sandbox->getHelper('input')->postInteger('budget');
				$record['msisdn'] = $this->sandbox->getHelper('input')->postString('msisdn');
				$record['notes'] = $this->sandbox->getHelper('input')->postString('notes');
				$record['latitude'] = $this->sandbox->getHelper('input')->postString('latitude');
				$record['longitude'] = $this->sandbox->getHelper('input')->postString('longitude');
				$record['province'] = $this->sandbox->getHelper('input')->postString('province');
				$record['county'] = $this->sandbox->getHelper('input')->postString('county');
				$record['district'] = $this->sandbox->getHelper('input')->postString('district');
				$record['facility'] = $this->sandbox->getHelper('input')->postString('facility');
				$record['sourceIP'] = $_SERVER['REMOTE_ADDR'];
				$record['creationTime'] = time();
				$payment = $this->sandbox->getLocalStorage()->insert(array('table' => 'apiOrder', 'content' => $record));
				error_log(json_encode($_POST), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				return json_encode(array('status' => 'success', 'tracking' => $payment), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
		}
	}
	
	protected function doShield(){
		if($headers['SpotCash-API-Key'] != '6992d57717d84869160cf44c60f10d02') {
			Response::sendHeader(403);
			exit;
		}		
	}
	
}