<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php
class Extendware_EWCore_Ewcore_System_CronjobController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/system/cronjob');
		$this->_title($this->__('System'))->_title($this->__('Manage Cronjobs'));
		parent::_construct();
	}

	public function getPersistentDataKey($key, $salt = null)
	{
		$append = '';
		$model = '';
		if (is_object($model)) $append .= $model->getId();
		return parent::getPersistentDataKey($key, (string) $append);
	}
	
	public function indexAction() {
		$this->_getModuleSession()->addNotice($this->__('This is a list of all Magento cronjobs. These cronjobs do not necessary relate to Extendware in any way.'));
		
	    $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_system_cronjob'));
 	    $this->renderLayout();
	}
	
	public function massDeleteAction() {
    	$ids = $this->getPost('ids');
    	
    	$passCount = $failCount = 0;
    	if(is_array($ids) and !empty($ids)) {
			foreach ($ids as $id) {
				try {
					$model = Mage::getModel('cron/schedule')->load($id);
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