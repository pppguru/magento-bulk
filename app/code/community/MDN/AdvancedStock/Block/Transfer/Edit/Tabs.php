<?php

class MDN_AdvancedStock_Block_Transfer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('advancedstock_transfer_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml()
    {
        
        $this->addTab('tab_main', array(
            'label'     => Mage::helper('AdvancedStock')->__('Information'),
            'content'   => $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_Main')->toHtml(),
        ));

        if ($this->getTransfer()->getId())
        {

            $this->addTab('tab_products', array(
                'label'     => Mage::helper('AdvancedStock')->__('Products'),
                'content'   => $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_Products')->toHtml()
                              ."<script>persistantProductGrid = new persistantGridControl(TransferProductsJsObject, 'product_log', 'stp_qty_requested', null);</script>",
            ));

            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stock_transfer/add_products'))
            {
                $this->addTab('tab_add_products', array(
                    'label'     => Mage::helper('AdvancedStock')->__('Add products'),
                    'content'   => $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_AddProducts')->toHtml()
                                  ."<script>persistantAddProductGrid = new persistantGridControl(TransferProductSelectionJsObject, 'add_product_log', 'add_qty_', null);</script>",
                ));
            }
            
            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stock_transfer/import_products'))
            {
                $this->addTab('tab_import_products', array(
                    'label'     => Mage::helper('AdvancedStock')->__('Import products'),
                    'content'   => $this->getLayout()->createBlock('AdvancedStock/Transfer_Edit_Tabs_ImportProducts')->toHtml(),
                ));
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     *
     *
     */
    public function getTransfer() {
        return mage::registry('current_transfer');
    }

}
