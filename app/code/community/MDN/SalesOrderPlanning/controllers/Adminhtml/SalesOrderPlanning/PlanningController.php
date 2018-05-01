<?php

class MDN_SalesOrderPlanning_Adminhtml_SalesOrderPlanning_PlanningController extends Mage_Adminhtml_Controller_Action
{
	
	/**
	 * Save planning
	 *
	 */
	public function SaveAction()
	{
		//retrieves object
		$planningId = $this->getRequest()->getPost('psop_id');
		$orderId = $this->getRequest()->getPost('psop_order_id');
		$order = mage::getModel('sales/order')->load($orderId);
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($planningId);
		
		//defines changes
		$considerationForceDateChange = false;
		$fullstockForceDateChange = false;
		$shippingForceDateChange = false;
		
		//store date to force
		if ($this->getRequest()->getPost('psop_consideration_date_force') != '')
		{
			$considerationForceDateChange = ($this->getRequest()->getPost('psop_consideration_date_force') != $planning->setpsop_consideration_date_force);
			$planning->setpsop_consideration_date_force($this->getRequest()->getPost('psop_consideration_date_force'));
		}
		else 
			$planning->setpsop_consideration_date_force(null);
			
		if ($this->getRequest()->getPost('psop_fullstock_date_force') != '')
		{
			$fullstockForceDateChange = ($this->getRequest()->getPost('psop_fullstock_date_force') != $planning->setpsop_fullstock_date_force);
			$planning->setpsop_fullstock_date_force($this->getRequest()->getPost('psop_fullstock_date_force'));
		}
		else 
			$planning->setpsop_fullstock_date_force(null);

		if ($this->getRequest()->getPost('psop_shipping_date_force') != '')
		{
			$shippingForceDateChange = ($this->getRequest()->getPost('psop_shipping_date_force') != $planning->setpsop_shipping_date_force);
			$planning->setpsop_shipping_date_force($this->getRequest()->getPost('psop_shipping_date_force'));
		}
		else 
			$planning->setpsop_shipping_date_force(null);

		//update planning depending of forced dates
		if ($considerationForceDateChange)
		{
			$planning->setConsiderationInformation($order);			
		}
		$planning->setFullStockInformation($order);
		$planning->setShippingInformation($order);
		$planning->setDeliveryInformation($order);
			
		$planning->save();
		
		
		
		//confirm
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Planning saved'));
    	
    	//Redirect
    	$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
	}
	
	/**
	 * Reset planning
	 *
	 */
	public function resetAction()
	{
		$planningId = $this->getRequest()->getParam('psop_id');

		//delete planning
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($planningId);
		$orderId = $planning->getpsop_order_id();
		mage::helper('SalesOrderPlanning/Planning')->createPlanningFromOrderId($orderId);
		
		//confirm
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Planning reseted'));
    	
    	//Redirect
    	$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId, 'tab' => 'sales_order_planning'));
	}
	
	/**
	 * Create planning
	 *
	 */
	public function CreateAction()
	{
		//create planning
		$orderId = $this->getRequest()->getParam('order_id');
		
		try 
		{
			$order = mage::getModel('sales/order')->load($orderId);
			if ($order->getId())
			{
				mage::helper('SalesOrderPlanning/Planning')->createPlanning($order);
			}
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Planning created'));
		}
		catch (Exception $ex)
		{
			Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
		}
		
		//redirect
    	$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    	
	}
	
	public function UpdateAction()
	{
		$planningId = $this->getRequest()->getParam('psop_id');
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($planningId);
		mage::helper('SalesOrderPlanning/Planning')->updatePlanning($planning->getpsop_order_id());
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Planning updated'));
		$this->_redirect('adminhtml/sales_order/view', array('order_id' => $planning->getpsop_order_id()));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/sales_order_planning/display_planning');
    }
}