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
class MDN_Purchase_Model_OrderProduct extends Mage_Core_Model_Abstract {

    private $_currency = null;
    private $_product = null;
    private $_purchaseOrder = null;

    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/OrderProduct');
    }

    public function getDiscountLevel()
    {
        if ($this->getPurchaseOrder()->getpo_default_product_discount() > 0)
            return $this->getPurchaseOrder()->getpo_default_product_discount();
        else
            return $this->getpop_discount();
    }

    /**
     * Row total (excluding taxes)
     *
     */
    public function getRowTotal() {
        $value = ($this->getpop_price_ht() + $this->getpop_eco_tax());
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        $value = $value * $this->getpop_qty();
        return round($value, 2);
    }

    /**
     * Return row weight
     */
    public function getRowWeight() {
        return round(($this->getpop_weight()) * $this->getpop_qty(), 2);
    }

    /**
     * Row total in base currency
     *
     */
    public function getRowTotal_base() {
        $value = ($this->getpop_price_ht_base() + $this->getpop_eco_tax_base());
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        $value = $value * $this->getpop_qty();
        return round($value, 2);
    }

    /**
     * Row total with taxes
     *
     */
    public function getRowTotalWithTaxes_base() {
        $tax_rate = $this->getpop_tax_rate();
        $value = ($this->getpop_price_ht_base() + $this->getpop_eco_tax_base());
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        $value = $value * (1 + $tax_rate / 100);
        $value = $value * $this->getpop_qty();
        return round($value, 2);
    }

    /**
     * Row total with taxes
     *
     */
    public function getRowTotalWithTaxes() {
        $tax_rate = $this->getpop_tax_rate();
        $value = ($this->getpop_price_ht() + $this->getpop_eco_tax());
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        $value = $value * (1 + $tax_rate / 100);
        $value = $value * $this->getpop_qty();
        return round($value, 2);
    }

    /**
     * Product with extended costs in base currency
     *
     */
    public function getUnitPriceWithExtendedCosts_base() {
        $value = $this->getpop_price_ht_base() + $this->getpop_eco_tax_base() + $this->getpop_extended_costs_base();
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        return round($value, 2);
    }

    /**
     * Product with extended costs
     *
     */
    public function getUnitPriceWithExtendedCosts() {
        $value = $this->getpop_price_ht() + $this->getpop_eco_tax() + $this->getpop_extended_costs();
        $value = $value * (1 - $this->getDiscountLevel() / 100);
        return round($value, 2);
    }

    /**
     * Return euro currency object
     *
     */
    public function getEuroCurrency() {
        //todo : deprecated
        return mage::getModel('directory/currency')->load('EUR');
    }

    /**
     * Return order currency
     *
     */
    public function getCurrency() {
        //todo : deprecated
        if ($this->_currency == null) {
            if ($this->getpo_currency() != '')
                $this->_currency = mage::getModel('directory/currency')->load($this->getpo_currency());
            else {
                $this->_currency = mage::getModel('directory/currency')->load('EUR');
            }
        }
        return $this->_currency;
    }

    /**
     * Update product delivered qty
     *
     */
    public function updateDeliveredQty() {
        $collection = mage::getModel('AdvancedStock/StockMovement')
                        ->getCollection()
                        ->addFieldToFilter('sm_po_num', $this->getpop_order_num())
                        ->addFieldToFilter('sm_product_id', $this->getpop_product_id());

        $sum = 0;
        foreach ($collection as $item) {
            $sum += $item->getsm_qty();
        }

        //if packaging enabled, convert value
        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $productId = $this->getpop_product_id();
            $sum = mage::helper('purchase/Product_Packaging')->convertSalesToUnit($productId, $sum);
        }

        $this->setpop_supplied_qty($sum)->save();
    }

    /**
     * return linked product
     *
     * @return unknown
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getpop_product_id());
        }
        return $this->_product;
    }

    /**
     * Get purchase order
     */
    public function getPurchaseOrder() {
        if ($this->_purchaseOrder == null) {
            $this->_purchaseOrder = mage::getModel('Purchase/Order')->load($this->getpop_order_num());
        }
        return $this->_purchaseOrder;
    }

    /**
     * Set purchase order
     */
    public function setPurchaseOrder($po) {
        $this->_purchaseOrder = $po;
    }

    /**
     * Return qty to delivered
     */
    public function getRemainingQty() {
        $value = $this->getOrderedQty() - $this->getSuppliedQty();
        if ($value < 0)
            $value = 0;
        return $value;
    }

    /**
     * Return ordered qty (in unit or package)
     */
    public function getOrderedQty() {
        $value = $this->getpop_qty();
        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $value = $value / $this->getpop_packaging_value();
        }
        return $value;
    }

    /**
     * Return supplied qty (unit or package)
     * @param <type> $item
     * @return <type>
     */
    public function getSuppliedQty() {
        $value = $this->getpop_supplied_qty();
        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $value = round($value / $this->getpop_packaging_value());
        }
        return $value;
    }

    /**
     * Before save
     *
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        //convert price, weee, extendedCosts to base currency
        $fields = array('pop_price_ht', 'pop_eco_tax', 'pop_extended_costs');
        $changeRate = $this->getPurchaseOrder()->getpo_currency_change_rate();
        if ($changeRate == 0)
            $changeRate = 1;
        foreach ($fields as $field) {
            $baseValue = $this->getData($field) / $changeRate;
            $this->setdata($field . '_base', $baseValue);
        }

        //if selected packaging has changed, save packaging datas
        if ($this->fieldHasChanged('pop_packaging_id')) {
            $packaging = mage::getModel('Purchase/Packaging')->load($this->getpop_packaging_id());
            $this->setpop_packaging_value($packaging->getpp_qty());
            $this->setpop_packaging_name($packaging->getpp_name());
        }

        //if no packaging id, set default values (mandatory to avoid UI issues)
        if ($this->getpop_packaging_id() == -1) {
            $this->setpop_packaging_value(1);
            $this->setpop_packaging_name('');
        }
    }

    /**
     * Method to define il a field value has changed
     *
     * @param unknown_type $fieldname
     * @return unknown
     */
    protected function fieldHasChanged($fieldname) {
        if ($this->getData($fieldname) != $this->getOrigData($fieldname))
            return true;
        else
            return false;
    }

}