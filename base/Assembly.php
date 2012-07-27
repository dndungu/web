<?php

namespace base;

class Assembly {
	
	protected $sandbox = NULL;
	
	protected $response = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('routing.passed', 'init', $this);
	}
	
	public function init($data) {
		$this->response = $data;
		try {
			$type = (string) $this->sandbox->getMeta('portal')->attributes()->type;
			switch($type){
				case "raw":
					$content = $this->toString();
					break;
				case "json":
					$content = $this->toJSON();
					break;
				case "xml":
					$content = $this->toXML();
					break;
				case "html":
					$this->response['base']['locale'] = $this->sandbox->getHelper('translation')->getLocale();
					$content = $this->toHTML();
					break;
			}
		}catch(BaseException $e){
			$message = $e->getMessage();
			error_log($message);
			return $this->sandbox->fire('assembly.failed', $message);
		}
		return $this->sandbox->fire('assembly.passed', $content);
	}
	
	protected function toString(){
		$string = array();
		foreach($this->response as $app){
			foreach($app as $controller => $content){
				$string[] = $content;
			}
		}
		return join("\n", $content);
	}
		
	protected function toJSON(){
		return json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
	
	protected function toXML($response = null, &$xml = null){
		if(is_null($xml)) {
			$xml = new \SimpleXMLElement("<?xml version='1.0'?><response></response>");
		}
		if(is_null($response)) {
			$response = $this->response;
		}
		if(is_array($response) || is_object($response)){
			$this->buildXML($response, $xml);
		}
		return $xml->asXML();
	}
	
	protected function buildXML(&$content, &$xml){
		foreach($content as $key => $value){
			$node = is_numeric($key) ? "node-$key" : $key;
			if(is_array($value) || is_object($value)){
				$child = $xml->addChild($node);
				$this->toXML($value, $child);
			}else{
				$text = htmlentities((string) $value);
				$xml->addChild($node, $text);
			}
		}
	}
	
	protected function toHTML(){
		try {
			$template = (string) $this->sandbox->getMeta('portal')->attributes()->template;
			$settings = $this->sandbox->getMeta('settings');
			$base = $this->sandbox->getMeta('base');
			$theme = $settings['theme'];
			$xslt = new \XsltProcessor();
			$site = $this->sandbox->getMeta('site');
			$home = $site['home'];
			$template = "$base/sites/$home/themes/$theme/$template";
			$xslt->importStylesheet(simplexml_load_file($template));
			$data = simplexml_load_string($this->toXML());
			$html = $xslt->transformToXML($data);
			return $html;
		} catch (\Exception $e) {
			throw new BaseException($e->getTraceAsString());
		} 
	}
	
}
?>