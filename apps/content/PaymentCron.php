<?php

namespace apps\content;

class PaymentCron extends \apps\Application {
		
	public function doGet(){
		$user = $this->sandbox->getHelper('user')->getID();
		$query = "INSERT INTO `payment` (`order_type`, `debit_amount`, `MSISDN`, `trx_desc`) (SELECT `orderType`, `approvalFiveAmount`, `MSISDN`, `notes` FROM `order` LEFT JOIN `beneficiary` ON (`order`.`beneficiary` = `beneficiary`.`ID`) ORDER BY `order`.`ID` DESC LIMIT 1)";
		return array('result' => $this->sandbox->getLocalStorage()->query($query));
	}
	
}