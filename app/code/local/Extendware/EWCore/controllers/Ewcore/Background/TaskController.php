<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php
class Extendware_EWCore_Ewcore_Background_TaskController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function indexAction()
	{

	}
	
	public function updateLicensesAndMessagesAction() {
		$this->updateLicensesAction();
		$this->updateMessagesAction();
		return $this;
	}
	
	public function updateLicensesAction() {
		try {
			if ($this->mHelper('config')->isUpdateLicensesOnAdminLoginEnabled() === true) {
				$modules = Mage::getSingleton('ewcore/module')->getCollection()->getItems();
		    	shuffle($modules);
				foreach ($modules as $module) {
					if ($module->isActive() === true) {
		    			$module->updateLicensesAndSerial();
					}
		    	}
			}
		} catch (Exception $e) {}
		return $this;
	}
	
	public function updateMessagesAction() {
		try {
			Mage::getResourceModel('ewcore/message')->update();
		} catch (Exception $e) {}
		return $this;
	}
}