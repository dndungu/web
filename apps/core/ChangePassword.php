<?php

namespace apps\core;

class ChangePassword extends \apps\Application {
	
	public function doGet(){
		try {
			$user = $this->sandbox->getHelper('user');
			if($user->isGuest() === 'Yes'){
				$translator = $this->sandbox->getHelper('translation');
				$page = $this->doSignInForm();
				$page['message'][] = $translator->translate('login.required');
			}else{
				$page = $this->doChangePasswordForm();
			}
			return $page;
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		try {
			$user = $this->sandbox->getHelper('user');
			$user->changePassword();
			$page = $this->doSignInForm();
			$translator = $this->sandbox->getHelper('translation');
			$page['message'][] = $translator->translate("password.changed");
			return $page;
		}catch (\apps\ApplicationException $e) {
			$page = $this->doChangePasswordForm();
			$page['error'][] = $e->getMessage();
			return $page;
		}
	}
	
	private function doChangePasswordForm(){
		$translator = $this->sandbox->getHelper('translation');
		$form = $this->sandbox->getHelper('form');
		$base = $this->sandbox->getMeta('base');
		$form->setSource("$base/apps/core/forms/changepassword.xml");
		$page['title'] = $translator->translate('changepassword');
		$page['body'] = $form->asHTML();
		return $page;
	}
	
	private function doSignInForm(){
		$translator = $this->sandbox->getHelper('translation');
		$form = $this->sandbox->getHelper('form');
		$base = $this->sandbox->getMeta('base');
		$form->setSource("$base/apps/core/forms/signin.xml");
		$form->setAction('/signin');
		$page['title'] = $translator->translate('signin');
		$page['content'] = $form->asHTML();
		return $page;
	}	
	
}