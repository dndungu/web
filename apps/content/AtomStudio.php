<?php

namespace apps\content;

class AtomStudio extends \apps\Application {
	
	public function doGet(){
		$t = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		$html[] = '<div class="dashboard">';
		$html[] = "\t".'<div class="atom atom-grey">';
		$utility = $this->sandbox->getLocalStorage()->query("SELECT `utility_balance` FROM `settings` LIMIT 1");
		$html[] = "\t\t".'<div class="content">'.number_format($utility[0]['utility_balance'], 2).'</div>';
		$html[] = "\t\t".'<div class="title">Utility Balance</div>';
		$html[] = "\t".'</div>';
		$html[] = "\t".'<div class="atom atom-grey">';
		$utility = $this->sandbox->getLocalStorage()->query("SELECT `solid_balance` FROM `settings` LIMIT 1");
		$html[] = "\t\t".'<div class="content">'.number_format($utility[0]['solid_balance'], 2).'</div>';
		$html[] = "\t\t".'<div class="title">Solid Balance</div>';
		$html[] = "\t".'</div>';
		$html[] = "<br/><br/><br/><br/><br/><hr/><h2>Payments Overview</h2>";
		$html[] = "\t".'<div class="atom atom-grey">';
		$pending = $this->sandbox->getLocalStorage()->query("SELECT COUNT(*) AS `rowCount` FROM `payment` WHERE `trx_status` NOT IN ('Completed', 'Failed') AND UNIX_TIMESTAMP(  `request_date` ) > $t");
		$html[] = "\t\t".'<div class="content">'.$pending[0]['rowCount'].'</div>';
		$html[] = "\t\t".'<div class="title">pending today</div>';
		$html[] = "\t".'</div>';
		$html[] = "\t".'<div class="atom atom-green">';
		$complete = $this->sandbox->getLocalStorage()->query("SELECT COUNT(*) AS `rowCount` FROM `payment` WHERE `trx_status` IN ('Completed', 'Failed') AND UNIX_TIMESTAMP(  `request_date` ) > $t");
		$html[] = "\t\t".'<div class="content">'.$complete[0]['rowCount'].'</div>';
		$html[] = "\t\t".'<div class="title">completed today</div>';
		$html[] = "\t".'</div>';
		$html[] = "\t".'<div class="atom atom-orange">';
		$reversed = $this->sandbox->getLocalStorage()->query("SELECT COUNT(*) AS `rowCount` FROM `payment` WHERE `trx_type` = 'Reversal' AND `trx_status` IN ('Completed') AND UNIX_TIMESTAMP(  `request_date` ) > $t");
		$html[] = "\t\t".'<div class="content">'.$reversed[0]['rowCount'].'</div>';
		$html[] = "\t\t".'<div class="title">reversed today</div>';
		$html[] = "\t".'</div>';
		$html[] = "\t".'<div class="atom atom-red">';
		$failed = $this->sandbox->getLocalStorage()->query("SELECT COUNT(*) AS `rowCount` FROM `payment` WHERE `trx_status` IN ('Failed') AND UNIX_TIMESTAMP(  `request_date` ) > $t");
		$html[] = "\t\t".'<div class="content">'.$failed[0]['rowCount'].'</div>';
		$html[] = "\t\t".'<div class="title">failed today</div>';
		$html[] = "\t".'</div>';
		$html[] = '</div>';
		return implode("\n", $html);
	}
	
}