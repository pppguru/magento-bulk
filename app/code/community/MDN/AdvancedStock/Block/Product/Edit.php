<?php

/**
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_AdvancedStock_Block_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    private $_product = null;

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'product';
        $this->_blockGroup = 'AdvancedStock';

        parent::__construct();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/save')) {
            $this->_updateButton('save', 'label', Mage::helper('AdvancedStock')->__('Save'));
            $this->_updateButton('save', 'onclick', 'beforeSaveProduct()');
        }
        else
            $this->_removeButton('save');

        $this->_removeButton('delete');

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/force_stock_update')) {
            if (Mage::helper('AdvancedStock/MagentoVersionCompatibility')->isQty($this->getProduct()->getTypeId())) {
                $this->_addButton(
                        'update_stocks', array(
                    'label' => Mage::helper('AdvancedStock')->__('Force Stocks Update'),
                    'onclick' => "window.location.href='" . $this->getUrl('adminhtml/AdvancedStock_Products/UpdateStock', array('product_id' => $this->getProduct()->getId())) . "'",
                    'level' => -1
                        )
                );
            }
        }

        $this->_addButton(
                'view_product', array(
            'label' => Mage::helper('AdvancedStock')->__('Magento View'),
            'onclick' => "window.location.href='" . $this->getUrl('adminhtml/catalog_product/edit', array('id' => $this->getProduct()->getId())) . "'",
            'level' => -1
                )
        );
    }

    public function getHeaderText() {
        $text = $this->getProduct()->getName();
        $text .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($this->getProduct()->getId());
        $text .= ' (' . $this->getProduct()->getsku() . ')';

        return $text;
    }

    /**
     * 
     *
     * @param unknown_type $value
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
        }
        return $this->_product;
    }

    public function getSaveUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Products/Save');
    }

    public function GetBackUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Products/Grid', array());
    }

}
