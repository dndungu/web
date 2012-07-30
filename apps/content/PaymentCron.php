<?php

namespace apps\content;

class PaymentCron extends \apps\Application {
	
	public function doPost(){
		if($this->sandbox->getHelper('input')->postString('command') != 'update') return;
		$user = $this->sandbox->getHelper('user')->getID();
		$query = sprintf("INSERT INTO `payment` (`user`, `order_type`, `debit_amount`, `MSISDN`, `trx_desc`) (SELECT %d, `orderType`, `approvalFiveAmount`, `MSISDN`, `notes` FROM `order` LEFT JOIN `beneficiary` ON (`order`.`beneficiary` = `beneficiary`.`ID`))", $user);
		$this->sandbox->getLocalStorage()->query($query);
	}
	
}