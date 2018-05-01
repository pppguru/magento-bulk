<?php


class MDN_ExtensionConflict_Adminhtml_ExtensionConflict_AdminController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * display list
	 *
	 */
	public function ListAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('system');

		$this->getLayout()->getBlock('head')->setTitle($this->__('Extension Conflict'));
		$block = $this->getLayout()->createBlock('ExtensionConflict/MainTab');

		$this->_addContent($block)->renderLayout();
	}
	
	/**
	 * Refresh list
	 *
	 */
	public function RefreshAction()
	{
		mage::helper('ExtensionConflict')->RefreshList();
		
		//redirect on list
	   	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('List refreshed'));
		$this->_redirect('adminhtml/ExtensionConflict_Admin/List');
	}
	
	/**
	 * Save file
	 *
	 */
	public function UploadAction()
	{
		//save file
	    $uploader = new Varien_File_Uploader('config_file');
	    $uploader->setAllowedExtensions(array('xml'));    		
    	$path = Mage::app()->getConfig()->getTempVarDir().'/ExtensionConflict/VirtualNamespace/VirtualModule/etc/';
	    $uploader->save($path);
    	
		//refresh list
		mage::helper('ExtensionConflict')->RefreshList();
		
		//redirect
	   	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('File Uploaded and List refreshed'));
		$this->_redirect('adminhtml/ExtensionConflict_Admin/List');
	}
	
	public function DeleteVirtualModuleAction()
	{
		//delete file
		$filePath = Mage::app()->getConfig()->getTempVarDir().'/ExtensionConflict/VirtualNamespace/VirtualModule/etc/config.xml';
		if (file_exists($filePath))
			unlink($filePath);	
		
		//refresh list
		mage::helper('ExtensionConflict')->RefreshList();
		
		//redirect
	   	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Virtual Module deleted and List refreshed'));
		$this->_redirect('adminhtml/ExtensionConflict_Admin/List');
	}
	
	/**
	 * Display conflict fix solution
	 *
	 */
	public function DisplayFixAction()
	{
		$this->loadLayout();
        $this->renderLayout();		
	}

	public function EnableDisableExtensionAction(){

		$extName = (string)$this->getRequest()->getParam('extName');
		$currentState = (string)$this->getRequest()->getParam('currentState');

		if(strlen($extName)>0 && strlen($currentState)>0) {
			try {
				if ($currentState == 'true') {
					mage::helper('ExtensionConflict/Extension')->disableExtension($extName);
					Mage::getSingleton('adminhtml/session')->addSuccess($extName . ' ' . $this->__('disabled'));
				}
				if ($currentState == 'false') {
					mage::helper('ExtensionConflict/Extension')->EnableExtension($extName);
					Mage::getSingleton('adminhtml/session')->addSuccess($extName . ' ' . $this->__('enabled'));
				}
				mage::helper('ExtensionConflict')->RefreshList();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Conflict list refreshed'));
			}catch(Exception $ex){
				Mage::getSingleton('adminhtml/session')->addError($ex->getMessage().' '.$ex->getTraceAsString());
			}
		}

		$this->_redirect('adminhtml/ExtensionConflict_Admin/List');
	}

	public function ComparerAjaxAction(){
		$this->getResponse()->setBody($this->getTemplate('Comparer'));
	}

	public function VirtualModuleAjaxAction(){
		$this->getResponse()->setBody($this->getTemplate('VirtualModule'));
	}

	public function ExtensionListAjaxAction(){
		$this->getResponse()->setBody($this->getTemplate('ExtensionList'));
	}

	public function BackupedConflictsAjaxAction(){
		$this->getResponse()->setBody($this->getTemplate('BackupedConflicts'));
	}

	public function EventsAjaxAction(){
		$this->getResponse()->setBody($this->getTemplate('Events'));
	}


	private function getTemplate($template){

		$extName = 'ExtensionConflict';

		return $this->getLayout()
				->createBlock($extName.'/List')
				->setTemplate($extName.'/'.$template.'.phtml')
				->toHtml();
	}


	public function CompareAjaxAction()
	{
		echo mage::helper('ExtensionConflict/Comparer')->compareExtension();
	}

	public function CleanComparerFolderAjaxAction()
	{
		echo mage::helper('ExtensionConflict/Comparer')->cleanComparerFolder();
	}

	public function BackupConflictAction(){

		$ecId = (string)$this->getRequest()->getParam('ec_id');

		try {

			mage::helper('ExtensionConflict/Backup')->backupConflict($ecId);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Conflict backuped'));

		}catch(Exception $ex){
			Mage::getSingleton('adminhtml/session')->addError($ex->getMessage().' '.$ex->getTraceAsString());
		}

		$this->_redirect('adminhtml/ExtensionConflict_Admin/List');
	}

	public function BackupDifferencesAjaxAction(){

		try {

			$differencesList = mage::helper('ExtensionConflict/Comparer')->getCompareExtensionList();

			mage::helper('ExtensionConflict/Backup')->backupDifferences($differencesList);

			echo $this->__('Differences backuped');

		}catch(Exception $ex){
			echo $ex->getMessage().' '.$ex->getTraceAsString();
		}

	}

	public function ObjectTraceAjaxAction(){

		$coreClass = (string)$this->getRequest()->getParam('core_class');
		$coreModule = (string)$this->getRequest()->getParam('core_module');

		$objectTrace = mage::helper('ExtensionConflict/ObjectTrace')->getObjectTrace($coreClass, $coreModule);

		$this->getResponse()->setBody($objectTrace);
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/system/extensionconflict');
	}
}
