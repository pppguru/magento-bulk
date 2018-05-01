<?php
class Bulksupplements_CustomReports_Adminhtml_BsReports_ProductsbykgController extends Mage_Adminhtml_Controller_Action {
	
	/*
	**Prior to SUPEE-6285 or Magento CE 1.9.2, magento responded to TRUE for _isAllowed if it was not overridden. But now the behavior has veen changed.
	**Hence users without administrator privilage does not have access to installed modules. 
	**Therefore, we should override _isAllowed to the child controller and use ACL path to allow access only to users who SHOULD have access.
	**Simply returning a true regardless of ACL path, it would be opened to anyone which will eventually nulify the security update.
	**July 28, 2015 - Mohin
	*/
	protected function _isAllowed() { 		
		return Mage::getSingleton('admin/session')->isAllowed('report/productsbykg');
	}
	
	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function exportCsvAction() {
		// Specify filename for exported CSV file
		$fileName = 'products_by_kg_report.csv';
		$content = $this->getLayout()->createBlock('customreports/adminhtml_productsbykg_grid')
		   ->getCsv();
		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction() {
		// Specify filename for exported XML file
		$fileName = 'report_new_orders.xml';
		$content = $this->getLayout()->createBlock('customreports/adminhtml_productsbykg_grid')
		   ->getXml();
		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK', '');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}

}
