<?php

class MDN_SalesOrderPlanning_Adminhtml_SalesOrderPlanning_ShippingDelayController extends Mage_Adminhtml_Controller_Action
{
	//Liste des taux de tax
	public function ListAction()
	{
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Shipping delays'));

        $this->renderLayout();
	}
	
	/**
	 * Update carriers
	 *
	 */
	public function UpdateCarriersAction()
	{
		mage::helper('SalesOrderPlanning/ShippingDelay')->updateCarriers();
		
	    //confirme
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Carriers list updated'));
    	
    	//Redirige vers la fiche creee
    	$this->_redirect('adminhtml/SalesOrderPlanning_ShippingDelay/List');
	}
	
	/**
	 * Save changes
	 *
	 */
	public function SaveAction()
	{
		//save values
		$collection = mage::getModel('SalesOrderPlanning/ShippingDelay')->getCollection();
		foreach ($collection as $item)
		{
			$id = $item->getId();
			$item->setpsd_default($this->getRequest()->getPost('psd_default'.$id));
			$item->setpsd_exceptions($this->getRequest()->getPost('psd_exceptions'.$id));
			$item->save();
		}
		
	    //confirme
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
    	
    	//Redirige vers la fiche creee
    	$this->_redirect('adminhtml/SalesOrderPlanning_ShippingDelay/List');
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/tools/purchase_shipping_delay');
    }
}