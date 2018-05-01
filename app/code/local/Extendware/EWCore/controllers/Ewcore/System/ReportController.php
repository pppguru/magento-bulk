<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_System_ReportController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/system/log/report');
		$this->_title($this->__('System'))->_title($this->__('Manage Reports'));
		parent::_construct();
	}
	
	protected function _initReportFile() {
		$model = Mage::registry('ew:current_report_file');
		if (!$model) {
			$model = Mage::getModel('ewcore/system_report_file')->loadById($this->getInput('id'));
	  		Mage::register('ew:current_report_file', $model);
		}
  		return $model;
	}
	
	public function getPersistentDataKey($key, $salt = null)
	{
		$append = '';
		$model = Mage::registry('ew:current_report_file');
		if (is_object($model)) $append .= $model->getId();
		return parent::getPersistentDataKey($key, (string) $append);
	}
	
	public function indexAction() {
	    $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_system_report'));
 	    $this->renderLayout();
	}

	
	public function editAction() {
		$id     = $this->getInput('id');
		$model  = $this->_initReportFile();
		
		if ($model->getId() and $model->getParsedData() !== false) {
			$this->_initAction();
				$this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_system_report_edit_tabs'));
	            $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_system_report_edit'));
	 	    $this->renderLayout();
	 	    return;
		} else {
			$this->_getModuleSession()->addError($this->__('Item does not exist or cannot be read'));
			return $this->_redirect('*/*/');
		}
	}

	public function downloadAction() {
		$id     = $this->getInput('id');
		$model  = $this->_initReportFile();
		
		
		if ($model->getId() and @file_exists($model->getPath()) === true) {
			header('Content-Description: File Transfer');
		    header('Content-Type: text/plain');
		    header('Content-Disposition: attachment; filename='.basename($model->getPath()));
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($model->getPath()));
		    ob_clean();flush();
			readfile($model->getPath());
			exit;
		} else {
			$this->_getModuleSession()->addError($this->__('Item does not exist'));
			return $this->_redirectReferer('*/*/');
		}
	}
	
    public function deleteAction() {
    	$model = $this->_initReportFile();
		if($model->getId()) {
			try {
				$model->delete();
					 
				$this->_getModuleSession()->addSuccess($this->__('Report file has been deleted'));
				return $this->_redirect('*/*/');
			} catch (Exception $e) {
				$this->_getModuleSession()->addError($e->getMessage());
				return $this->_redirect('*/*/edit', array('_current' => true));
			}
		}
		
		return $this->_redirect('*/*/');
	}
	
	public function massDeleteAction() {
    	$ids = $this->getPost('ids');
    	
    	$passCount = $failCount = 0;
		if(is_array($ids) and !empty($ids)) {
			foreach ($ids as $id) {
				try {
					$model = Mage::getModel('ewcore/system_report_file')->loadById($id);
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
		
		if (!$passCount and !$failCount) {
			$this->_getModuleSession()->addNotice($this->__('Please select an item(s) before submitting to the mass processor'));
		}
		
		return $this->_redirect('*/*/');
	}
	
}