<?php

class Bulksupplements_ReportDetails_Adminhtml_ReportdetailsController

	  extends Mage_Adminhtml_Controller_Action {



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

		$fileName = 'report_new_orders.csv';

		$content = $this->getLayout()->createBlock('reportdetails/adminhtml_child_grid')

		   ->getCsv();

		$this->_sendUploadResponse($fileName, $content);

	}



	public function exportXmlAction() {

		// Specify filename for exported XML file

		$fileName = 'report_new_orders.xml';

		$content = $this->getLayout()->createBlock('reportdetails/adminhtml_child_grid')

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

