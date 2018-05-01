<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_OrderController extends Mage_Adminhtml_Controller_Action {

    public function UpdateIsValidAction() {

        //retrieve information
        $orderId = $this->getRequest()->getParam('order_id');
        $order = mage::getModel('sales/order')->load($orderId);

        //update is valid
        mage::helper('AdvancedStock/Sales_ValidOrders')->updateIsValid($order, true);

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order validity updated'));
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }

}