<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Block_Product_Edit_Tabs_SalesHistory extends Mage_Adminhtml_Block_Widget_Form {

    private $_history = null;

    /**
     * Product get/set
     *
     * @var unknown_type
     */
    private $_product = null;

    public function setProduct($Product) {
        $this->_product = $Product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    /**
     * Constructeur
     *
     */
    public function __construct() {
        parent::__construct();

        $this->setTemplate('AdvancedStock/Product/Edit/Tab/SalesHistory.phtml');
    }

    /**
     * History
     */
    public function getHistory() {
        if ($this->_history == null) {
            $this->_history = mage::getModel('AdvancedStock/SalesHistory')
                ->getCollection()
                ->addFieldToFilter('sh_product_id', $this->getProduct()->getId())
                ->join('cataloginventory/stock', 'sh_stock_id=stock_id');
        }
        return $this->_history;
    }

    /**
     *
     */
    public function getRefreshHistoryUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_SalesHistory/RefreshForProduct', array('product_id' => $this->getProduct()->getId()));
    }

    public function getRefreshHistoryByWarehouseUrl($stockId) {
        return $this->getUrl('adminhtml/AdvancedStock_SalesHistory/RefreshForProductWarehouse',
            array('product_id' => $this->getProduct()->getId(),
                  'stock_id' => $stockId )
        );
    }

    /**
     *
     */
    public function getApplyPreferedStockLevelSuggestion() {
        return $this->getUrl('adminhtml/AdvancedStock_PreferedStockLevel/ApplyForProduct', array('product_id' => $this->getProduct()->getId()));
    }

    /**
     *
     */
    public function getRanges() {
        return mage::helper('AdvancedStock/Sales_History')->getRanges();
    }

    /**
     * Return suggestion for prefered stock level by warehouse
     *
     */
    public function getPreferedStockLevelSuggestion($warehouseId) {
        return mage::helper('AdvancedStock/Product_PreferedStockLevel')->getSuggestion($this->getProduct()->getId(), $warehouseId);
    }

}