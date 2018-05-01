<?php

/**
 * admin product edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_AdvancedStock_Block_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    private $_product = null;

    public function __construct() {
        parent::__construct();
        $this->setId('advancedstock_product_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml() {
        $product = $this->getProduct();
        $manageStocks = mage::helper('AdvancedStock/Product_Base')->ManageStock($product->getId());

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/stock')) {
            $this->addTab('tab_stock', array(
                'label' => Mage::helper('AdvancedStock')->__('Stock'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_Stock')->setProduct($this->getProduct())->toHtml(),
            ));
        }

        /*
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/graph')) {
            if ($manageStocks) {

                $this->addTab('tab_graph', array(
                    'label' => Mage::helper('AdvancedStock')->__('Stock Graph'),
                    'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_StockGraph')->setProduct($this->getProduct())->toHtml(),
                ));
            }
        }
        */

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/price')) {
            $this->addTab('tab_price', array(
                'label' => Mage::helper('AdvancedStock')->__('Price'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_Price')->setProduct($this->getProduct())->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/pending_sales_order')) {
            $this->addTab('tab_pending_sales_order', array(
                'label' => Mage::helper('AdvancedStock')->__('Pending sales order'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_PendingSalesOrder')->setProduct($this->getProduct())->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/barcode')) {
            $this->addTab('tab_barcode', array(
                'label' => Mage::helper('AdvancedStock')->__('Barcode'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_Barcode')->setProduct($this->getProduct())->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history')) {
            $this->addTab('tab_history', array(
                'label' => Mage::helper('AdvancedStock')->__('Sales History'),
                'content' => $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_SalesHistory')->setProduct($this->getProduct())->toHtml(),
            ));
        }

        //dispatch event to allow other extension to add their own tabs
        Mage::dispatchEvent('advancedstock_product_edit_create_tabs', array('tab' => $this, 'product' => $this->getProduct(), 'layout' => $this->getLayout()));

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'tab_stock';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }

    /**
     * Retrive product object from object if not from registry
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
        }
        return $this->_product;
    }

}
