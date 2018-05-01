<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_System_InformationController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/system/information');
		$this->_title($this->__('System'))->_title($this->__('View Information'));
		parent::_construct();
	}

	public function getPersistentDataKey($key, $salt = null)
	{
		$append = '';
		return parent::getPersistentDataKey($key, (string) $append);
	}
	
	public function indexAction() {
	    $this->_forward('edit');
	}

	public function editAction() {
		if ($this->mHelper('environment')->isDemoServer() === true) {
			$this->_getSession()->addError($this->__('Viewing system information is disabled in the demo'));
			return $this->_redirect('adminhtml/dashboard/index');
		}
		
		$this->_initAction();
			$this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit_tabs'));
            $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit'));
 	    $this->renderLayout();
	}
	
	public function phpInfoAction() {
		phpinfo();
		exit;
	}
	
	public function rewritesGridAction()
    {
		$html = '';
		$grid = $this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit_tab_rewrites');
		$html = $grid->toHtml();
		return $this->getResponse()->setBody($html);
    }
    
	public function eventsGridAction()
    {
		$html = '';
		$grid = $this->getLayout()->createBlock('ewcore/adminhtml_system_information_edit_tab_events');
		$html = $grid->toHtml();
		return $this->getResponse()->setBody($html);
    }
}