<?php

class MDN_AdvancedStock_Adminhtml_AdvancedStock_DebugController extends Mage_Adminhtml_Controller_Action {

    public function DebugAction() {
        die('This menu is reserved to BoostMyShop for debug purposes.');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management');
    }

}
