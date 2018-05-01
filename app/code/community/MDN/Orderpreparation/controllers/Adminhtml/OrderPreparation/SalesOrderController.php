<?php
/*
 * Created on Jun 26, 2008
 *
 */

class MDN_Orderpreparation_Adminhtml_OrderPreparation_SalesOrderController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Reserve product for order
	 *
	 */
	public function ReserveProductAction()
	{
		$productId = $this->getRequest()->getParam('product_id');
		$orderId = $this->getRequest()->getParam('order_id');
		$orderItemId = $this->getRequest()->getParam('order_item_id');
		
		try 
		{
    		$order = mage::getModel('sales/order')->load($orderId);
    		$orderItem = mage::getModel('sales/order_item')->load($orderItemId);
			mage::helper('AdvancedStock/Product_Reservation')->reserveOrderProduct($order, $orderItem);
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product reserved'));
		}
		catch (Exception $ex)
		{
		
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));	
		}
		
		$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
	}
	
	/**
	 * Release product for order
	 *
	 */
	public function ReleaseProductAction()
	{
		$productId = $this->getRequest()->getParam('product_id');
		$orderId = $this->getRequest()->getParam('order_id');
		$orderItemId = $this->getRequest()->getParam('order_item_id');
		
		try 
		{
    		$order = mage::getModel('sales/order')->load($orderId);
    		$orderItem = mage::getModel('sales/order_item')->load($orderItemId);
			mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $orderItem);
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product released'));
		}
		catch (Exception $ex)
		{
			
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));	
		}
		
		$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
	}
	
	/**
	 * Release all products for one order
	 *
	 */
	public function ReleaseAllProductsAction()
	{
		$orderId = $this->getRequest()->getParam('order_id');
		$order = mage::getModel('sales/order')->load($orderId);
		
		try 
		{
			foreach ($order->getAllItems() as $item)
			{
				mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);
			}
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Products released'));						
		}
		catch (Exception $ex)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));				
		}
		
		$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
	}
	
	/**
	 * Reserve all products for one order
	 *
	 */
	public function ReserveAllProductsAction()
	{
		$orderId = $this->getRequest()->getParam('order_id');
		$order = mage::getModel('sales/order')->load($orderId);
		
		try 
		{
			foreach ($order->getAllItems() as $item)
			{
				mage::helper('AdvancedStock/Product_Reservation')->reserveOrderProduct($order, $item);
			}
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Products reserved'));						
		}
		catch (Exception $ex)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));				
		}
		
		$this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation');
    }
}