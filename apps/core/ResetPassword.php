<?php

namespace apps\core;

class ResetPassword extends \apps\Application {
	
	public function doGet(){
		try {
			$page = $this->doResetPasswordForm();
			return $page;
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		$translator = $this->sandbox->getHelper('translation');
		$user = $this->sandbox->getHelper('user');
		$recoverID = $user->resetPassword();
		$page = $this->doResetPasswordForm();
		$page['message'][] = $translator->translate('resetpassword.checkemail');
		return $page;
	}
	
	private function doResetPasswordForm(){
		$translator = $this->sandbox->getHelper('translation');
		$form = $this->sandbox->getHelper('form');
		$base = $this->sandbox->getMeta('base');
		$form->setSource("$base/apps/core/forms/resetpassword.xml");
		$page['title'] = $translator->translate('resetpassword');
		$page['body'] = $form->asHTML();
		return $page;
	}	
	
}