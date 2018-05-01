<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs_TabComplete extends Mage_Adminhtml_Block_Widget
{


    public function __construct()
    {
        parent::__construct();
        $this->setHtmlId('tabcontent');
        $this->setTemplate('ProductReturn/SupplierReturn/Tabs/TabComplete.phtml');
    }

    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabCompleteGrid');
        $block->setTemplate('ProductReturn/SupplierReturn/Tabs/TabCompleteGrid.phtml');
        $block->setStatus('complete');
        $this->setChild('supplierreturn_gridcomplete',
            $block
        );
    }


}