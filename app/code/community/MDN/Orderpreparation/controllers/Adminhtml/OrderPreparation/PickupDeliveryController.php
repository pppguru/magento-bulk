<?php

class MDN_Orderpreparation_Adminhtml_OrderPreparation_PickupDeliveryController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Pickup delivery'));

        $this->renderLayout();
    }

    /**
     * Send email to customer to notify that his order is available for pickup
     */
    public function NotifyAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = mage::getModel('sales/order')->load($orderId);

        mage::helper('Orderpreparation/PickupDeliveryOrders')->notify($order);

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer notified'));
        $this->_redirect('adminhtml/OrderPreparation_PickupDelivery/Grid');
    }

    public function massNotifyAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        foreach ($orderIds as $orderId) {
            $order = mage::getModel('sales/order')->load($orderId);
            mage::helper('Orderpreparation/PickupDeliveryOrders')->notify($order);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customers notified'));
        $this->_redirect('adminhtml/OrderPreparation_PickupDelivery/Grid');
    }

    public function PickupFormAction() {
        //set order as picked up
        $orderId = $this->getRequest()->getParam('order_id');
        $order = mage::getModel('sales/order')->load($orderId);
        $order->setpickup_is_picked(1);
        $order->setpickup_information('Order picked up on ' . date('Ym-d H:i'));
        $order->save();

        //add organizer
        $Task = Mage::getModel('Organizer/Task')
                ->setot_author_user(10)
                ->setot_created_at(date('Y-m-d H:i'))
                ->setot_caption('Customer picked order on '.date('Y-m-d H:i'))
                ->setot_description('')
                ->setot_entity_type('order')
                ->setot_entity_id($order->getId())
                ->setot_entity_description('Order #'.$order->getincrement_id())
                ->save();

        //download pickup PDF
        $obj = mage::getModel('Orderpreparation/Pdf_OrderPickup');
        $pdf = $obj->getPdf(array($order));
        $this->_prepareDownloadResponse('order_pickup.pdf', $pdf->render(), 'application/pdf');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation');
    }

}