<?php

class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs_TabNew extends Mage_Adminhtml_Block_Widget
{


    public function __construct()
    {
        parent::__construct();
        $this->setHtmlId('tabcontent');
        $this->setTemplate('ProductReturn/SupplierReturn/Tabs/TabNew.phtml');
    }

    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabNewGrid');
        $block->setTemplate('ProductReturn/SupplierReturn/Tabs/TabNewGrid.phtml');
        $block->setStatus('new');
        $this->setChild('supplierreturn_gridnew',
            $block
        );
    }


}