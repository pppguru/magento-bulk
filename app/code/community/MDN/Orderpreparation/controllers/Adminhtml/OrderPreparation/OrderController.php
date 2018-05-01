<?php

class MDN_Orderpreparation_Adminhtml_OrderPreparation_OrderController extends Mage_Adminhtml_Controller_Action {

    public function DispatchAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $result = Mage::helper('Orderpreparation/Dispatcher')->DispatchOrder($order);

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Order distribute'));
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation');
    }

}
