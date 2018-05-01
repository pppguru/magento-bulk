<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_Content_PageController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('extendware/manage_module');
		$this->_title($this->__('Extendware'))->_title($this->__('View Content'));
		parent::_construct();
	}
	
	public function viewAction()
	{
		$template = str_replace('_', DS, preg_replace('/([^a-z0-9_])/i', '', $this->getInput('id')));
		$block = $this->getLayout()->createBlock('adminhtml/template');
		$block->setTemplate('extendware'.DS.'ewcore'.DS.'content'.DS.'page' . DS . str_replace('_', DS, $this->getInput('id')) . '.phtml');
		if (file_exists(realpath(Mage::getBaseDir('design')) . DS. $block->getTemplateFile()) === false) {
			return $this->norouteAction();
		}
		
	    $this->_initAction();
		    $this->_addContent($block);
 	    $this->renderLayout();
	}
}