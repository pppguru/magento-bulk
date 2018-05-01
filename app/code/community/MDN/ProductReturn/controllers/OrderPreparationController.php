<?php

class MDN_ProductReturn_OrderPreparationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Ajax grid in order preparation screen
     */
    public function GridAction()
    {
        $this->loadLayout();
        $Block = $this->getLayout()
            ->createBlock('ProductReturn/OrderPreparation_Grid');
        $this->getResponse()->setBody($Block->toHtml());
    }

    /**
     * Add rma to selected orders
     */
    public function AddToSelectionAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        try {
            //add to selected orders
            Mage::getModel('Orderpreparation/ordertoprepare')->AddSelectedOrder($orderId);

            //confirm & redirect
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rma successfully added.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        $this->_redirect('OrderPreparation/OrderPreparation/');

    }
}