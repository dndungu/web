<?php

namespace apps\core;

class L18n extends \apps\Application {
	public function doGet(){
		$locale = $this->sandbox->getHelper('translation')->getLocale();
		$page[] = "core.l18n = ";
		$page[] = json_encode($locale, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$page[] = ";";
		header('Content-type: application/json');
		return join("", $page);
	}
} 