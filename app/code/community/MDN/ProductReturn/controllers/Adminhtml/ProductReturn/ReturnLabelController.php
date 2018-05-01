<?php

class MDN_ProductReturn_Adminhtml_ProductReturn_ReturnLabelController extends Mage_Adminhtml_Controller_Action
{

    public function DeleteAction()
    {
        $rma = Mage::getModel('ProductReturn/Rma')->load($this->getRequest()->getParam('rma_id'));
        Mage::helper('ProductReturn/Returnlabel')->deleteLabel($rma);

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Label deleted'));
        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rma->getId()));
    }

    /**
     * Send return label email to customer
     *
     */
    public function NotifyAction()
    {
        $rma = Mage::getModel('ProductReturn/Rma')->load($this->getRequest()->getParam('rma_id'));

        try {


            if (!Mage::helper('ProductReturn/Returnlabel')->isExists($rma))
                throw new Exception($this->__('No return label found'));

            Mage::helper('ProductReturn/Returnlabel')->notifyLabel($rma);

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer successfully notified.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to notify customer') . ' : ' . $ex->getMessage());
        }

        $this->_redirect('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $rma->getId()));
    }

   protected function _isAllowed()
   {
       return true;
   }


}