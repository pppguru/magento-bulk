<?php

class MDN_ProductReturn_Block_Admin_ProductsPendingSupplierReturn_Edit extends Mage_Adminhtml_Block_Widget_Form
{

    protected $_rsrp = null;

    protected function _construct()
    {

        parent::_construct();

        $this->getRsrp();

        $this->setTemplate('ProductReturn/ProductsPendingSupplierReturn/Edit.phtml');

    }

    public function getRsrp()
    {
        if ($this->_rsrp == null) {
            $rsrpId      = $this->getRequest()->getParam('rsrp_id');
            $this->_rsrp = Mage::getModel('ProductReturn/SupplierReturn_Product')->load($rsrpId);
            mage::register('current_rsrp', $this->_rsrp);
        }

        return $this->_rsrp;
    }
}
