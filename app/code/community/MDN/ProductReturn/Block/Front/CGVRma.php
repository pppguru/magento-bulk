<?php

class MDN_ProductReturn_Block_Front_CGVRma extends Mage_Core_Block_Template
{
    private $_productReturn = null;

    public function getRma()
    {
        if ($this->_productReturn == null) {
            $productReturnId      = $this->getRequest()->getParam('rma_id');
            $this->_productReturn = mage::getModel('ProductReturn/Rma')->load($productReturnId);
        }

        return $this->_productReturn;
    }


    public function getReturnPrintUrl()
    {
        return $this->getUrl('ProductReturn/Front/PrintPdf');
    }


}