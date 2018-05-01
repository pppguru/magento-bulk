<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs_TabSentToSupplier extends Mage_Adminhtml_Block_Widget
{


    public function __construct()
    {
        parent::__construct();
        $this->setHtmlId('tabcontent');
        $this->setTemplate('ProductReturn/SupplierReturn/Tabs/TabSentToSupplier.phtml');
    }

    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabSentToSupplierGrid');
        $block->setTemplate('ProductReturn/SupplierReturn/Tabs/TabSentToSupplierGrid.phtml');
        $block->setStatus('sent_to_supplier');
        $this->setChild('supplierreturn_gridsenttosupplier',
            $block
        );
    }


}