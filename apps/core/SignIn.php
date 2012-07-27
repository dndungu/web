<?php

namespace apps\core;

class SignIn extends \apps\Application {
	
	public function doGet(){
		try {
			$page = $this->doSignInForm();
			return $page;
		} catch (\apps\ApplicationException $e) {
			$this->doCrash($e);
		}
	}
	
	public function doPost(){
		try{
			$user = $this->sandbox->getHelper('user');			
			$user->signIn();
			$this->doRedirect();
		}catch(\apps\ApplicationException $e){
			$page = $this->doSignInForm();
			$page['error'][] = $e->getMessage();
			return $page;
		}
	}
	
	private function doSignInForm(){
		$translator = $this->sandbox->getHelper('translation');
		$form = $this->sandbox->getHelper('form');
		$base = $this->sandbox->getMeta('base');
		try {
			$form->setSource("$base/apps/core/forms/signin.xml");
			$page['title'] = $translator->translate('signin');
			$page['body'] = $form->asHTML();
			return $page;
		}catch(HelperException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
}