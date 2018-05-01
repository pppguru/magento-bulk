<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_CacheController extends Extendware_EWCore_Controller_Adminhtml_Action
{
    public function flushAction()
    {
        try {
        	$this->mHelper('cache')->clean();
			$this->_getSession()->addSuccess($this->__('The cache has been flushed'));
        } catch (Exception $e) {
        	Mage::logException($e);
			$this->_getSession()->addException($e, $this->__('An error occurred while flushing the cache'));
        }
        return $this->_redirectReferer('*/cache/index');
    }
}
