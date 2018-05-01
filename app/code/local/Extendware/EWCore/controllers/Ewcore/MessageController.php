<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_MessageController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/message');
		$this->_title($this->__('Manage Messages'));
		parent::_construct();
	}
	
	protected function _initMessage() 
	{
		$model = Mage::registry('ew:current_message');
		if ($model === null) {
			$model = Mage::getModel('ewcore/message')->loadById($this->getInput('id'));
	  		Mage::register('ew:current_message', $model);
		}
		
  		return $model;
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
        $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_message'));
 	    $this->renderLayout();
	}

	
	public function editAction() {
		$id     = $this->getInput('id');
		$model  = $this->_initMessage();
		
		if ($model->getId()) {
			if ($model->getUrl() and !$model->getBody()) {
				return $this->getResponse()->setHeader('Location', $model->getUrl());
			} else {
				$this->_initAction();
					$this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_message_edit_tabs'));
		            $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_message_edit'));
		 	    $this->renderLayout();
			}
	 	    return;
		} else {
			$this->_getModuleSession()->addError($this->__('Item does not exist or cannot be read'));
			return $this->_redirect('*/*/');
		}
	}
	
	public function updateAction() {
		try {
			if (Mage::getResourceModel('ewcore/message')->update(true) === false) {
				Mage::throwException($this->__('Failed to update messages'));
			}
			$this->_getModuleSession()->addSuccess($this->__('Messages have been updated'));
		} catch (Exception $e) {
			$this->_getModuleSession()->addError($e->getMessage());
		}
		
		return $this->_redirect('*/*/index');
	}
	
	public function gotoAction() {
		$id     = $this->getInput('id');
		$model  = $this->_initMessage();
		
		if ($model->getId()) {
			return $this->getResponse()->setHeader('Location', $model->getUrl());
		} else {
			$this->_getModuleSession()->addError($this->__('Item does not exist or cannot be read'));
			return $this->_redirect('*/*/');
		}
	}
	
    public function deleteAction() {
    	$model = $this->_initMessage();
		if($model->getId()) {
			try {
				$model->delete();
					 
				$this->_getModuleSession()->addSuccess($this->__('Message has been deleted'));
				return $this->_redirect('*/*/');
			} catch (Exception $e) {
				$this->_getModuleSession()->addError($e->getMessage());
				return $this->_redirectReferer('*/*/');
			}
		}
		
		return $this->_redirectReferer('*/*/');
	}
	
	public function markReadAction() {
    	$model = $this->_initMessage();
		if($model->getId()) {
			try {
				$model->setState('read');
				$model->save();
					 
				$this->_getModuleSession()->addSuccess($this->__('Message has been marked as read'));
				return $this->_redirect('*/*/');
			} catch (Exception $e) {
				$this->_getModuleSession()->addError($e->getMessage());
				return $this->_redirectReferer('*/*/');
			}
		}
		
		return $this->_redirectReferer('*/*/');
	}
	
	public function massStateAction() {
    	$ids = $this->getPost('ids');
    	$state = $this->getPost('state');
		if(is_array($ids) and !empty($ids) and $state) {
			$passCount = $failCount = 0;
			foreach ($ids as $id) {
				try {
					$model = Mage::getModel('ewcore/message')->loadById($id);
					$model->setState($state);
					$model->save();
					$passCount++;
				} catch (Exception $e) {
					$failCount++;
				}
			}
			
			$processor = str_replace('_', ' ', Mage::helper('ewcore/utility')->camelCaseToUnderscore(preg_replace('/Action$/', '', preg_replace('/^mass/', '', __FUNCTION__))));
			if ($passCount > 0) $this->_getModuleSession()->addSuccess($this->__('Selected item(s) (%d) have been mass processed by the %s processor', $passCount, $processor));
			if ($failCount > 0) $this->_getModuleSession()->addError($this->__('Selected item(s) (%d) have failed to be mass processed by the %s processor', $failCount, $processor));
		}
		
		return $this->_redirect('*/*/');
	}
	
	public function massDeleteAction() {
    	$ids = $this->getPost('ids');
    	if(is_array($ids) and !empty($ids)) {
    		$passCount = $failCount = 0;
			foreach ($ids as $id) {
				try {
					$model = Mage::getModel('ewcore/message')->loadById($id);
					$model->delete();
					$passCount++;
				} catch (Exception $e) {
					$failCount++;
				}
			}
			
			$processor = str_replace('_', ' ', Mage::helper('ewcore/utility')->camelCaseToUnderscore(preg_replace('/Action$/', '', preg_replace('/^mass/', '', __FUNCTION__))));
			if ($passCount > 0) $this->_getModuleSession()->addSuccess($this->__('Selected item(s) (%d) have been mass processed by the %s processor', $passCount, $processor));
			if ($failCount > 0) $this->_getModuleSession()->addError($this->__('Selected item(s) (%d) have failed to be mass processed by the %s processor', $failCount, $processor));
		}
		
		return $this->_redirect('*/*/');
	}
	
}