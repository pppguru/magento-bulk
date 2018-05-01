<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_StockMovementController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Return stock movement grid for product in ajax
	 *
	 */
	public function ProductStockMovementGridAction()
	{
		
    	$this->loadLayout();
     	$productId = $this->getRequest()->getParam('product_id');
     	$product = mage::getModel('catalog/product')->load($productId);
		$Block = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_StockMovements');
		$Block->setProduct($product);
        $this->getResponse()->setBody($Block->toHtml());
	}
	
	/**
	 * Validation stock movement creation (check if qty are available)
	 *
	 */
	public function ValidateAction()
	{
		//retrieve datas
		$productId = $this->getRequest()->getPost('sm_product_id');
		$sourceWarehouse = $this->getRequest()->getPost('sm_source_stock');
		$targetWarehouse = $this->getRequest()->getPost('sm_target_stock');
		$qty = $this->getRequest()->getPost('sm_qty');
		$description = $this->getRequest()->getPost('sm_description');
		
		//check
		$error = 0;
		$message = '';
		
		try 
		{
			if ($description == '')
				throw new Exception($this->__('Please fill description'));
			
			$model = mage::getModel('AdvancedStock/StockMovement');
			$model->validateStockMovement($productId, $sourceWarehouse, $targetWarehouse, $qty);
		}
		catch (Exception $ex)
		{
			$error = 1;
			$message = $ex->getMessage();
		}

   		//return ajax result 
   		$response = array();
   		$response['error'] = $error;
   		$response['message'] = $message;
   		$response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
	}
	
	/**
	 * Create stock movement
	 *
	 */
	public function CreateAction()
	{
		//retrieve datas
		$productId = $this->getRequest()->getPost('sm_product_id');
		$sourceWarehouse = $this->getRequest()->getPost('sm_source_stock');
		$targetWarehouse = $this->getRequest()->getPost('sm_target_stock');
		$qty = $this->getRequest()->getPost('sm_qty');
		$description = $this->getRequest()->getPost('sm_description');
		$type = $this->getRequest()->getPost('sm_type');
		
		//check
		$error = 0;
		$message = '';
		
		try 
		{
			$additionalData = array('sm_type' => $type);
			$model = mage::getModel('AdvancedStock/StockMovement');
			$model->createStockMovement($productId, 
										$sourceWarehouse, 
										$targetWarehouse, 
										$qty, 
										$description, 
										$additionalData);
		}
		catch (Exception $ex)
		{
			$error = 1;
			$message = $ex->getMessage();
		}

		
   		//return ajax result 
   		$response = array();
   		$response['error'] = $error;
   		$response['message'] = $message;
   		$response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
	}
	
	/**
	 * Display all stock movements
	 *
	 */
	public function GridAction()
	{
		$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Stock Movements'));

		$this->renderLayout();
	}
	
	/**
	 * Delete stock movement
	 *
	 */
	public function DeleteAction()
	{
		$smId = $this->getRequest()->getParam('sm_id');
		$stockMovement = mage::getModel('AdvancedStock/StockMovement')->load($smId);
		$stockMovement->delete();
		
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Stock Movement deleted')); 
		$this->_redirect('adminhtml/AdvancedStock_StockMovement/Grid');

	}

	public function DownloadReasonAction()
	{
		$smId = $this->getRequest()->getParam('sm_id');
		$stockMovement = mage::getModel('AdvancedStock/StockMovement_Adjustment')->load($smId, 'sm_id');
		$logs = str_replace("\n", "\r\n", $stockMovement->getlog());
		$this->_prepareDownloadResponse('Stock movement #'.$smId.'.txt', $logs, 'text/text');

	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }
}