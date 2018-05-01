<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_ModuleController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function _construct()
	{
		$this->setMenuPath('ewcore/quickaccess_overview');
		$this->_title($this->__('Extendware'))->_title($this->__('Manage Software'));
		Mage::getResourceModel('ewcore/module_summary')->refreshAll();
		parent::_construct();
	}
	
	protected function _initModuleSummary() 
	{
		$model = Mage::registry('ew:current_module_summary');
		if ($model === null) {
			if ($this->getInput('id')) {
				$model = Mage::getModel('ewcore/module_summary')->loadById($this->getInput('id'));
			} elseif ($this->getInput('identifier')) {
				$model = Mage::getModel('ewcore/module_summary')->loadByIdentifier($this->getInput('identifier'));
			}
	  		Mage::register('ew:current_module_summary', $model);
		}
		
  		return $model;
	}
	
	public function getPersistentDataKey($key, $salt = null)
	{
		$append = '';
		$model = Mage::registry('ew:current_module_summary');
		if (is_object($model)) $append .= $model->getId();
		return parent::getPersistentDataKey($key, (string) $append);
	}
	
	public function indexAction()
	{
	    $this->_initAction();
		    $this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_module_grid_tabs'));
		    $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_module_grid'));
 	    $this->renderLayout();
	}
	
	public function editAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		$this->_initAction();
			$this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_module_edit_tabs'));
            $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_module_edit'));
		$this->renderLayout();
	}
	
	protected function getSettingsXml($moduleName) {
		$file = Mage::getConfig()->getModuleDir(null, $moduleName);
		$file .= DS . 'etc' . DS . 'extendware' . DS . 'settings.xml';
		$xml = simplexml_load_file($file);
		if (!$xml) return false;
		
		return $xml->modules->{$moduleName}->extendware->settings->scopes;
	}
	
	public function resetConfigAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		if ($moduleSummary->getModule()->isExtendware() === false) {
			$this->_getModuleSession()->addError($this->__('Only Extendware extensions can have their config reset'));
			return $this->_redirect('*/*/index');
		}
		
		$options = array();
		$scopes = $this->getSettingsXml($moduleSummary->getIdentifier());
		foreach ($scopes->children() as $scopeKey => $scope) {
			foreach ($scope->sections->children() as $sectionKey => $section) {
				$value = $scopeKey . '::' . $sectionKey;
				$label = $this->__((string)$scope->tabs->main->label) . '->' . $this->__((string)$section->label);
				$options[$value] = $label;
			}
		}
		
		if (empty($options) === true) {
			$this->_getModuleSession()->addError($this->__('Extension does not have any configuration to reset'));
			return $this->_redirect('*/*/index');
		}
		
		Mage::register('ew:current_module_section_options', Mage::getModel('ewcore/data_option_custom')->setOptions($options));
		
		$this->_getModuleSession()->addNotice($this->__('This will restore default extension configuration options by deleting custom settings from the database. Please backup prior to using this tool.'));
		
		$this->_initAction();
			$this->_addLeft($this->getLayout()->createBlock('ewcore/adminhtml_module_config_edit_tabs'));
            $this->_addContent($this->getLayout()->createBlock('ewcore/adminhtml_module_config_edit'));
		$this->renderLayout();
	}
	
	public function resetConfigSaveAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		$general = $this->getPost('general');
		$sectionPaths = (array)$general['sections'];
		if (empty($sectionPaths)) {
			$this->_getModuleSession()->addError($this->__('At least one section must be selected'));
			return $this->_redirect('*/*/resetConfig', array('id' => $moduleSummary->getId()));
		}
		
		$scopes = array();
		foreach ($sectionPaths as $path) {
			list($scope, $section) = explode('::', $path, 2);
			if ($scope and $section) {
				if (isset($scopes[$scope]) === false) $scopes[$scope] = array();
				$scopes[$scope][$section] = $section;
			}
		}
		
		$paths = array();
		$scopesXml = $this->getSettingsXml($moduleSummary->getIdentifier());
		foreach ($scopesXml->children() as $scopeKey => $scopeXml) {
			if (isset($scopes[$scopeKey]) === false) continue;
			$scope = $scopes[$scopeKey];
			foreach ($scopeXml->sections->children() as $sectionKey => $sectionXml) {
				if (isset($scope[$sectionKey]) === false) continue;
				foreach ($sectionXml->groups->children() as $groupKey => $groupXml) {
					foreach ($groupXml->fields->children() as $fieldKey => $fieldXml) {
						$path = $sectionKey . '/' . $groupKey . '/' . $fieldKey;
						$paths[] = $path;
					}
				}
			}
		}
		
		$count = 0;
		foreach ($paths as $path) {
			$configCollection = Mage::getModel('core/config_data')->getCollection();
			$configCollection->addFieldToFilter('path', $path);
			foreach ($configCollection as $item) {
				$item->delete();
				$count++;
			}
		}
		
		Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
            
		$this->_getSession()->addSuccess($this->__('There were %s config items deleted', $count));
		if ($moduleSummary->getIdentifier() == 'Extendware_EWPageCache') {
			return $this->_redirect('adminhtml/ewpagecache_config/autoConfigureCallback');
		}
		$configureUrl = $moduleSummary->getModule()->getConfigureRoute();
		if ($configureUrl) {
			$this->_redirect($configureUrl);
			return;
		}
		return $this->_redirect('*/*/index');
	}
	
	public function saveAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}

		if ($moduleSummary->getModule()->canToggleModuleStatus() === false) {
			$this->_getModuleSession()->addError($this->__('Cannot change module status. Please manually disable module'));
			if ($this->mHelper('environment')->isDemoServer() === true) {
				$this->_getModuleSession()->addNotice($this->__('Changing module status is disabled in the demo'));
			}
			return $this->_redirect('*/*/edit', array('_current' => true));
		}
		
		$data = $this->getPost();
		$status = @$data['general']['status'] == 'disabled' ? false : true;
		
		$didUpdate = false;
		$errors = $notices = array();
		try {
			$configTools = Mage::helper('ewcore/config_tools');
			if ($status === true) {
				$configTools->enableModule($moduleSummary->getIdentifier());
			} else $configTools->disableModule($moduleSummary->getIdentifier());
			Mage::app()->cleanCache();
			$didUpdate = true;
		} catch (Exception $e) {
			$errors['update_error'] = 'Could not update module status. Please ensure config files are writeable and look in Extendware -> System -> Logs -> Logs in <u>ewcore.log</u> for more detailed information.';
			Mage::logException($e);
		}
		
		if ($didUpdate === true) {
			$this->_getModuleSession()->addSuccess($this->__('Module has been saved'));
			if ($status === true) {
				$this->_getModuleSession()->addNotice($this->__('If you enabled an extension, you will need to logout and re-login before you can access it.'));
			}
		}
		
		foreach ($errors as $error) {
			$this->_getModuleSession()->addError($this->__($error));
		}
		
		foreach ($notices as $notice) {
			$this->_getModuleSession()->addNotice($this->__($notice));
		}
		
		if (is_null($this->getInput('back'))) {
        	return $this->_redirect('*/*/');
        }
	        
		return $this->_redirect('*/*/edit', array('id' => $moduleSummary->getId()));
	}
	
	public function configureAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		$configureUrl = $moduleSummary->getModule()->getConfigureRoute();
		if ($configureUrl) {
			$this->_redirect($configureUrl);
			return;
		} else {
			$this->_getModuleSession()->addNotice($this->__('Module does not have anything to configure'));
			return $this->_redirect('*/*/index');
		}
	}
	
	public function updateLicensesAndSerialAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		try {
			$lastLogMessage = $this->__('[none]');
			$collection = Mage::getModel('ewcore/system_log')->getFileCollection(true);
			foreach ($collection as $log) {
				if (strpos($log->getRelativePath(), 'ewcore.log') === false) continue;
				if (filesize($log->getPath()) > 1024*1024) continue;
				$lineCollection = $log->getLineCollection();
				$line = $lineCollection->getFirstItem();
				$lastLogMessage = $line->getMessage();
			}
			
			if ($moduleSummary->getModule()->updateLicensesAndSerial(true) === false) {
				Mage::throwException($this->__('Failed to update extension license. Please check <u>ewcore.log</u> in <a href="%s">Extendware -> System -> Logs -> Logs</a> for more information [<a href="%s">click here</a>]. The last log message is: %s', $this->getUrl('adminhtml/ewcore_system_log/index'), $this->getUrl('adminhtml/ewcore_system_log/index'), $lastLogMessage));
			}
			$this->_getModuleSession()->addSuccess($this->__('Module serial and licenses have been updated'));
		} catch (Exception $e) {
			$this->_getModuleSession()->addError($e->getMessage());
		}
		
		return $this->_redirect('*/*/edit', array('_current' => true));
	}
	
	public function massUpdateLicensesActionAction() {
    	$ids = $this->getPost('ids');
    	
    	$passCount = $failCount = $skipCount = 0;
		if (is_array($ids) and !empty($ids)) {
			foreach ($ids as $id) {
				try {
					$model = Mage::getModel('ewcore/module_summary')->loadById($id);
					$module = $model->getModule();
					if ($module->isActive() === false or $module->isLicensed() === false) {
						$skipCount++;
						continue;
					}

					try {
						$res = $module->updateLicensesAndSerial(true);
						if ($res) $passCount++;
						else $failCount++;
					} catch (Exception $e) {
						$failCount++;
					}
				} catch (Exception $e) {
					$failCount++;
				}
			}
			
			$processor = str_replace('_', ' ', Mage::helper('ewcore/utility')->camelCaseToUnderscore(preg_replace('/Action$/', '', preg_replace('/^mass/', '', __FUNCTION__))));
			if ($passCount > 0) $this->_getModuleSession()->addSuccess($this->__('Selected item(s) (%d) have been mass processed by the %s processor', $passCount, $processor));
			if ($failCount > 0) $this->_getModuleSession()->addError($this->__('Selected item(s) (%d) have failed to be mass processed by the %s processor', $failCount, $processor));
			if ($skipCount > 0) $this->_getModuleSession()->addNotice($this->__('Selected item(s) (%d) have been skipped by the %s processor', $skipCount, $processor));
		}
		
		if (!$passCount and !$failCount) {
			$this->_getModuleSession()->addNotice($this->__('Please select an item(s) before submitting to the mass processor'));
		}
		
		return $this->_redirect('*/*/');
	}
	
	public function getMissingLicensesAndSerialAction()
	{
		$moduleSummary = $this->_initModuleSummary();
		if (!$moduleSummary->getId()) {
			return $this->_redirect('*/*/index');
		}
		
		try {
			if ($moduleSummary->getModule()->fetchMissingLicensesAndSerial() === false) {
				Mage::throwException($this->__('Failed to get all licenses and serial for module'));
			}
			$this->_getModuleSession()->addSuccess($this->__('Module serial and licenses have been fetched. There is no guarantee the right files were fetched. Try and enable the extension.'));
		} catch (Exception $e) {
			$this->_getModuleSession()->addError($e->getMessage());
		}
		
		return $this->_redirect('*/*/edit', array('_current' => true));
	}
	
	public function extendwareTabAction()
	{
		$this->_initAjaxAction();
		$this->_initLayoutMessages('adminhtml/session');
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('ewcore/adminhtml_module_grid_tab_extendware')->toHtml()
		);
	}
	
	public function systemTabAction()
	{
		$this->_initAjaxAction();
		$this->_initLayoutMessages('adminhtml/session');
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('ewcore/adminhtml_module_grid_tab_system')->toHtml()
		);
	}
	
	public function thirdPartyTabAction()
	{
		$this->_initAjaxAction();
		$this->_initLayoutMessages('adminhtml/session');
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('ewcore/adminhtml_module_grid_tab_thirdparty')->toHtml()
		);
	}
	
	public function allTabAction()
	{
		$this->_initAjaxAction();
		$this->_initLayoutMessages('adminhtml/session');
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('ewcore/adminhtml_module_grid_tab_all')->toHtml()
		);
	}
	
	public function exportExtendwareModulesToCsvAction()
    {
    	$this->_exportToCsv('extendware_modules.csv', 'ewcore/adminhtml_module_grid_tab_extendware');
    }


    public function exportExtendwareModulesToXmlAction()
    {
    	$this->_exportToXml('extendware_modules.xml', 'ewcore/adminhtml_module_grid_tab_extendware');
    }
    
	public function exportThirdPartyModulesToCsvAction()
    {
    	$this->_exportToCsv('third_party_modules.csv', 'ewcore/adminhtml_module_grid_tab_thirdparty');
    }


    public function exportThirdPartyModulesToXmlAction()
    {
    	$this->_exportToXml('third_party_modules.xml', 'ewcore/adminhtml_module_grid_tab_thirdparty');
    }
    
	public function exportSystemModulesToCsvAction()
    {
    	$this->_exportToCsv('system_modules.csv', 'ewcore/adminhtml_module_grid_tab_system');
    }


    public function exportSystemModulesToXmlAction()
    {
    	$this->_exportToXml('system_modules.xml', 'ewcore/adminhtml_module_grid_tab_system');
    }
    
	public function exportAllModulesToCsvAction()
    {
    	$this->_exportToCsv('all_modules.csv', 'ewcore/adminhtml_module_grid_tab_all');
    }


    public function exportAllModulesToXmlAction()
    {
    	$this->_exportToXml('all_modules.xml', 'ewcore/adminhtml_module_grid_tab_all');
    }
}