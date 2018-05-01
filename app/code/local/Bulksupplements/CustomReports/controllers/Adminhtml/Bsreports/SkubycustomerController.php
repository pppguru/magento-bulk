<?php
class Bulksupplements_CustomReports_Adminhtml_BsReports_SkubycustomerController extends Mage_Adminhtml_Controller_Action 
{	
	//Override _isAllowed to the child controller and use ACL path to allow access only to users who SHOULD have access. Look at (SUPEE-6285 or Magento CE 1.9.2) release note for more details
	protected function _isAllowed() { 		
		return Mage::getSingleton('admin/session')->isAllowed('report/skubycustomer');
	}
	
	public function indexAction() {					
        $this->loadLayout();	
		$isAjax = $this->getRequest()->getParam('isAjax');
		if($isAjax){
			$this->renderGridOnly();
		}
		else{
			$block = $this->getLayout()->createBlock('customreports/adminhtml_skubycustomer', 'skubycustomer', array());
			$this->getLayout()->getBlock('content')->append($block);
			$this->renderLayout();
		}
	}
	
	public function loadReportAction(){	
		$this->retrieveAndRegisterParams();		
		$isAjax = $this->getRequest()->getParam('isAjax');
		if($isAjax){
			$this->renderGridOnly();
		}
		else{
			$this->loadLayout();		 
			$block = $this->getLayout()->createBlock('customreports/adminhtml_skubycustomer', 'skubycustomer', array());
			$this->getLayout()->getBlock('content')->append($block);
			$this->renderLayout();
		}
	}
	
	public function exportCsvAction()
	{		
		$this->retrieveAndRegisterParams();
		$fileName   = 'skubycustomre_report_'.date('Y-m-d H:i:s').'.csv';
		$content    = $this->getLayout()->createBlock('customreports/adminhtml_skubycustomer_components_customergrid_grid', 'skubycustomer_grid_block')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	
	private function renderGridOnly()
	{
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('customreports/adminhtml_skubycustomer_components_customergrid_grid', 'skubycustomer_grid_block', array())->toHtml()
		);		
	}
	private function retrieveAndRegisterParams()
	{
		$sku = $this->getRequest()->getParam('product_sku');
		$fromDate = $this->getRequest()->getParam('from_date');
		$toDate = $this->getRequest()->getParam('to_date');
		
		Mage::register('product_sku', $sku);
		Mage::register('from_date', $fromDate);
		Mage::register('to_date', $toDate);
	}
}

