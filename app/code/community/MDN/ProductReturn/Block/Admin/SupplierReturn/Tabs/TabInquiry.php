<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs_TabInquiry extends Mage_Adminhtml_Block_Widget
{


    public function __construct()
    {
        parent::__construct();
        $this->setHtmlId('tabcontent');
        $this->setTemplate('ProductReturn/SupplierReturn/Tabs/TabInquiry.phtml');
    }

    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabInquiryGrid');
        $block->setTemplate('ProductReturn/SupplierReturn/Tabs/TabInquiryGrid.phtml');
        $block->setStatus('inquiry');
        $this->setChild('supplierreturn_gridinquiry',
            $block
        );
    }


}