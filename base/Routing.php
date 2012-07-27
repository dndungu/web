<?php

namespace base;

class Routing {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('authentication.passed', 'init', $this);
	}
	
	public function init($portal) {
		try {
			$base = $this->sandbox->getMeta("base");
			require_once("$base/apps/Application.php");
			foreach($portal->portlet as $portlet){
				$module = (string) $portlet->attributes()->module;
				$controller = (string) $portlet->attributes()->controller;
				$content = $this->route($module, $controller);
				$response[$module][$controller][] = $content;
			}
			$this->sandbox->fire('routing.passed', $response);
		} catch(BaseException $e) {
			$message = $e->getMessage();
			return $this->sandbox->fire('routing.failed', $message);
		}
	}
	
	protected function sourceFile($module, $controller) {
		$dir = $this->sandbox->getMeta('base')."/apps/$module";
		if(!is_dir($dir)) {
			throw new BaseException("Module '$module' does not exists");
		}
		$source = "$dir/$controller.php";
		if(!file_exists($source)){
			throw new BaseException("App controller '$source' does not exists");
		}
		if(!is_readable($source)) {
			throw new BaseException("App controller '$source' is not readable");
		}
		return $source;
	}
	
	protected function route($module, $controller) {
		$source = $this->sourceFile($module, $controller);
		require_once($source);
		$portlet = "apps\\$module\\$controller";
		if(!class_exists($portlet)) {
			throw new BaseException("Portlet controller '$portlet' class does not exist");
		}
		$instance = new $portlet($this->sandbox);
		$method = $this->sandbox->getMeta('method');
		if(!method_exists($instance, $method)) {
			throw new BaseException("Portlet controller '$method' method does not exist");
		}
		return call_user_func_array(array($instance, $method), array());
	}
	
}

?>