<?php

class MDN_Purchase_Adminhtml_Purchase_SupplierInvoiceController extends Mage_Adminhtml_Controller_Action
{

	public function ListAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Supplier Invoices'));

        $this->renderLayout();
	}

	public function EditAction()
	{
		$psi_id = $this->getRequest()->getParam('psi_id');

		$block = $this->getLayout()->createBlock('Purchase/SupplierInvoice_Edit');
		$block->loadSupplierInvoice($psi_id);
		$block->setTemplate('Purchase/SupplierInvoice/Edit.phtml');

		$this->getResponse()->setBody($block->toHtml());
	}

	public function NewAction()
	{
		$po_num = $this->getRequest()->getParam('po_num');

		$block = $this->getLayout()->createBlock('Purchase/SupplierInvoice_Edit');
		$block->setPurchaseOrderId($po_num);
		$block->setTemplate('Purchase/SupplierInvoice/Edit.phtml');

		$this->getResponse()->setBody($block->toHtml());
	}

	public function SaveAction()
	{
		$data = $this->getRequest()->getPost();
		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice');

		$isNew = true;

		//Edit case
		if(array_key_exists('psi_id',$data) && $data['psi_id'] != '' && is_numeric($data['psi_id']) && $data['psi_id']>0) {
			$supplierInvoice = $supplierInvoice->load($data['psi_id']);
			$isNew = false;
		}

		//edit or create case
		$skippedKey = array('psi_id');
		foreach ($data as $key => $value)
			if(!in_array($key,$skippedKey))
				$supplierInvoice->setData($key, $value);

		//update attachment (if exists)
		if(array_key_exists('psi_attachment',$_FILES)){
			try {
				if (strlen($_FILES['psi_attachment']['name'])>0) {
					$fileName = $_FILES['psi_attachment']['name'];
					$fileType = ($_FILES['psi_attachment']['type'])?$_FILES['psi_attachment']['type']:'application/pdf';

					if (file_exists($_FILES['psi_attachment']['tmp_name'])) {
						$attachmentContent = file_get_contents($_FILES['psi_attachment']['tmp_name']);
					} else {
						$attachmentContent = file_get_contents($_FILES['psi_attachment']['name']);
					}

					$supplierInvoice->setpsi_attachment($attachmentContent);
					$supplierInvoice->setpsi_attachment_name($fileName);
					$supplierInvoice->setpsi_attachment_type($fileType);
				}

			} catch (Exception $ex) {
				//die($ex->getMessage());
			}
		}
		$supplierInvoice->save();

		$createdMessage = ($isNew)?$this->__('created'):$this->__('modified');
		$msg = $this->__('Supplier Invoice %s as been '.$createdMessage,$supplierInvoice->getpsi_id());
		Mage::helper('purchase/Order')->addHistoryMessage($supplierInvoice->getpsi_po_id(), $msg);

    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

		$this->redirectDepedingContext();
	}

	public function DeleteAttachmentAction()
	{
		$id = $this->getRequest()->getParam('psi_id');

		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($id);
		$supplierInvoice->setpsi_attachment(null);
		$supplierInvoice->setpsi_attachment_name(null);
		$supplierInvoice->setpsi_attachment_type(null);
		$supplierInvoice->save();

		$msg = $this->__('Supplier Invoice attachment %s as been deleted',$supplierInvoice->getpsi_id());
		Mage::helper('purchase/Order')->addHistoryMessage($supplierInvoice->getpsi_po_id(), $msg);
		Mage::getSingleton('adminhtml/session')->addSuccess($msg);

		$this->redirectDepedingContext();
	}

	public function DownloadAttachmentAction()
	{
		$id = $this->getRequest()->getParam('psi_id');
		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($id);
		$mimeType = ($supplierInvoice->getpsi_attachment_type())?$supplierInvoice->getpsi_attachment_type():'application/pdf';
		$fileName = ($supplierInvoice->getpsi_attachment_name())?$supplierInvoice->getpsi_attachment_name():$this->__('Invoice#%s',$id).'.pdf';
		header('Content-disposition: attachment; filename='.$fileName);
		header('Content-type: ' . $mimeType);
		echo $supplierInvoice->getpsi_attachment();
		die();
	}



	public function DeleteAction()
	{
		$psi_id = $this->getRequest()->getParam('psi_id');

		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($psi_id);

		if($supplierInvoice->getId()>0){
			$msg = $this->__('Supplier Invoice %s deleted',$psi_id);
			Mage::helper('purchase/Order')->addHistoryMessage($supplierInvoice->getpsi_po_id(), $msg);
			$supplierInvoice->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
		}else{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Supplier Invoice not found'));
		}
    	
    	$this->redirectDepedingContext();
	}

	public function ExportCsvAction(){
		$fileName = 'export_supplier_invoices.csv';
		$content = $this->getLayout()->createBlock('Purchase/SupplierInvoice_Grid')
				->getCsv();

		$this->_prepareDownloadResponse($fileName, $content);
	}



	private function redirectDepedingContext(){
		$refererUrl = $this->getRequest()->getOriginalRequest()->getHeader('Referer');
		if ($refererUrl && strpos($refererUrl, '/List/') > 0) {
			$this->_redirect('adminhtml/Purchase_SupplierInvoice/List');
		}else{
			$currentEditingPoId = $this->getRequest()->getParam('po_num');
			if($currentEditingPoId){
				$this->_redirect('adminhtml/Purchase_Orders/Edit', array('po_num' => $currentEditingPoId, 'tab' => 'tab_supplier_invoices'));
			}else{
				$this->_redirect('adminhtml/Purchase_SupplierInvoice/List');
			}
		}
	}

	public function PurchaseOrderPopupSearchAction()
	{
		$start = $this->getRequest()->getParam('start', 1);
		$limit = $this->getRequest()->getParam('limit', 15);
		$query = $this->getRequest()->getParam('query', '');

		$searchInstance = new MDN_Purchase_Model_Order_PoSearch();

		$items = $searchInstance->setStart($start)
			->setLimit($limit)
			->setQuery($query)
			->load()
			->getResults();

		$block = $this->getLayout()->createBlock('adminhtml/template')
			->setTemplate('Purchase/Order/Search/Autocomplete.phtml')
			->assign('items', $items);


		$this->getResponse()->setBody($block->toHtml());
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
		//return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/supplier_invoices');
	}
}
