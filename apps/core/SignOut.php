<?php

namespace apps\core;

class SignOut extends \apps\Application {
	
	public function doGet(){
		try{
			$this->sandbox->getHelper('session')->purge();
			$user = $this->sandbox->getHelper('user');
			$translator = $this->sandbox->getHelper('translation');
			$form = $this->sandbox->getHelper('form');
			$base = $this->sandbox->getMeta('base');
			$form->setSource("$base/apps/core/forms/signin.xml");
			$form->setAction('/signin');
			$page['title'] = $translator->translate('signin');
			$page['body'] = $form->asHTML();
			$page['message'][] = $translator->translate("user.sign.out");
			return $page;
		}catch(\apps\ApplicationException $e){
			$this->onError($e);
		}
	}
			
}