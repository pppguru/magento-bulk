<?php

class MDN_Scanner_Block_Inventory_EditStock extends Mage_Adminhtml_Block_Widget_Form {

    private $_stock = null;
    private $_warehouse = null;
    private $_stockMovements = null;
    private $_product = null;

    /**
     * Return stock
     *
     * @return unknown
     */
    public function getStock() {
        if ($this->_stock == null) {
            $stockId = $this->getRequest()->getParam('stock_id');
            $this->_stock = mage::getModel('cataloginventory/stock_item')->load($stockId);
        }
        return $this->_stock;
    }

    /**
     * Return warehouse
     *
     * @return unknown
     */
    public function getWarehouse() {
        if ($this->_warehouse == null) {
            $warehouseId = $this->getStock()->getstock_id();
            $this->_warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId);
        }
        return $this->_warehouse;
    }

    /**
     * Return stock movements
     *
     * @return unknown
     */
    public function getStockMovements() {
        if ($this->_stockMovements == null) {
            $productId = $this->getStock()->getproduct_id();
            $this->_stockMovements = mage::getModel('AdvancedStock/StockMovement')
                            ->getCollection()
                            ->addFieldToFilter('sm_product_id', $productId)
                            ->setOrder('sm_date', 'desc');
        }
        return $this->_stockMovements;
    }

    /**
     * Return product
     *
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getStock()->getproduct_id());
        }
        return $this->_product;
    }

    /**
     * Url to submit form
     *
     * @return unknown
     */
    public function getSubmitUrl() {
        return $this->getUrl('adminhtml/Scanner_Inventory/SaveStockQty');
    }

    public function getImageUrl() {
        $url = '';
        if (($this->getProduct()->getsmall_image() != null) && ($this->getProduct()->getsmall_image() != 'no_selection')) {
            $url = mage::helper('catalog/image')->init($this->getProduct(), 'small_image')->resize(50);
        } else {
            //try to get picture from parent
            $configurableProduct = $this->getConfigurableProduct($this->getProduct());
            if ($configurableProduct) {
                if (($configurableProduct->getsmall_image() != null) && ($configurableProduct->getsmall_image() != 'no_selection')) {
                    $url = mage::helper('catalog/image')->init($configurableProduct, 'small_image')->resize(50);
                }
            }
        }
        return $url;
    }

    private function getConfigurableProduct($product)
    {
		$parentIdArray = mage::helper('AdvancedStock/MagentoVersionCompatibility')->getProductParentIds($product);
    	foreach ($parentIdArray as $parentId)
    	{
    		$parent = mage::getModel('catalog/product')->load($parentId);
    		return $parent;
    	}

    	return null;
    }


}