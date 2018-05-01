<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_UserguideController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/userguide');
		$this->_title($this->__('View User Guides'));
		parent::_construct();
	}
	
	public function getPersistentDataKey($key, $salt = null)
	{
		$append = '';
		$model = Mage::registry('ew:current_message');
		if (is_object($model)) $append .= $model->getId();
		return parent::getPersistentDataKey($key, (string) $append);
	}
	
	public function indexAction() {
	    $this->_initAction();
	    $this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_userguide_edit_tabs'));
        $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_userguide_edit'));
 	    $this->renderLayout();
	}
	
	public function viewGuideAction()
    {
		$html = '';
		$block = $this->getLayout()->createBlock('ewcore/adminhtml_userguide_edit_tab_general');
		$block->setModule(Mage::getSingleton('ewcore/module')->load($this->getInput('id')));
		return $this->getResponse()->setBody($block->toHtml());
    }
}