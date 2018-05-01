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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_RmaProducts extends Mage_Core_Model_Abstract
{

    const kDestinationStock    = 'Back to stock';
    const kDestinationSupplier = 'Back to supplier';
    const kDestinationCustomer = 'Back to customer';
    const kDestinationDestroy  = 'None';

    private $_rma = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/RmaProducts');
    }

    /**
     * Return possible destination for 1 product line
     *
     */
    public function getDestinations()
    {
        $destinations                             = array();
        $destinations[]                           = '';

        if(Mage::Helper('ProductReturn')->erpIsInstalled()){

            foreach(Mage::getModel('AdvancedStock/Warehouse')->getCollection()
                ->addFieldToFilter('stock_is_hidden', 0) as $warehouse){

                $destinations['warehouse_'.$warehouse->getId()] = Mage::Helper('ProductReturn')->__('Back to '.$warehouse->getstock_name());

            }

        }else{

            $destinations[self::kDestinationStock]    = mage::helper('ProductReturn')->__(self::kDestinationStock);

        }

        $destinations[self::kDestinationSupplier] = mage::helper('ProductReturn')->__(self::kDestinationSupplier);
        $destinations[self::kDestinationCustomer] = mage::helper('ProductReturn')->__(self::kDestinationCustomer);
        $destinations[self::kDestinationDestroy]  = mage::helper('ProductReturn')->__(self::kDestinationDestroy);

        return $destinations;
    }

    /**
     * Return reasons array
     *
     * @return unknown
     */
    public function getReasons($storeId = 0)
    {
        $retour = array();

        //get reasons in configuration
        $other_reason = Mage::getStoreConfig('productreturn/product_return/other_reason', $storeId);
        $array_other_reason = explode(';', $other_reason);

        if (is_array($array_other_reason)) {
            foreach ($array_other_reason as $reason) {
                if (!empty($reason))
                    $retour [$reason] = $reason;
            }
        }

        return $retour;
    }

    /**
     * Return request type array
     *
     * @return unknown
     */
    public function getRequesttype($storeId = 0)
    {
        $retour = array();

        //get request type in configuration
        $request_types      = Mage::getStoreConfig('productreturn/product_return/request_type', $storeId);
        $array_request_type = explode(';', $request_types);

        if (is_array($array_request_type)) {
            foreach ($array_request_type as $request_type) {
                if (!empty($request_type))
                    $retour [$request_type] = $request_type;
            }
        }

        return $retour;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $product
     *
     * @return unknown
     */
    public function productIsDisplayed($product)
    {
        $retour = true;

        switch ($product->getproduct_type()) {
            case 'bundle':
                $retour = true;
                break;
            case 'configurable':
                $retour = true;
                break;
            default:
                //load order item
                $orderItemid = $product->getitem_id();
                if ($orderItemid) {
                    $orderItem = mage::getModel('sales/order_item')->load($orderItemid);
                    if ($orderItem->getId()) {
                        if ($orderItem->getparent_item_id()) {
                            //do not display product with parent = configurable
                            $parentItemId = $orderItem->getparent_item_id();
                            $parentItem   = mage::getModel('sales/order_item')->load($parentItemId);
                            if (($parentItem) && ($parentItem->getproduct_type() == 'configurable'))
                                $retour = false;
                        }
                    }
                }
                break;
        }

        return $retour;
    }

    /**
     * 
     * @param type $product = order item
     * @param type $html
     * @return type
     */
    public function getProductName($product, $html = true)
    {
        $value = '<b>' . $product->getname() . '</b>';
        if ($product->getproduct_type() == 'configurable') {
            //add sub products
            $collection = mage::getModel('sales/order_item')
                ->getCollection()
                ->addFieldToFilter('parent_item_id', $product->getitem_id());
            foreach ($collection as $subProduct) {
                $value .= '<br><i>' . $subProduct->getname() . '</i>';

                //add product configurable attributes values
                $attributesDescription = mage::helper('ProductReturn/Configurable')->getDescription($subProduct->getproduct_id(), $product->getrp_product_id());
                if ($attributesDescription != '')
                    $value .= '<br>' . $attributesDescription;
            }
        }

        if (!$html)
        {
            $value = str_replace('<br>', "\n", $value);
            $value = strip_tags($value);
        }
        
        return $value;
    }

    /**
     * Return true if has sub product
     */
    public function hasSubProduct()
    {
        $product = mage::getModel('catalog/product')->load($this->getrp_product_id());
        if ($product->gettype_id() == 'configurable')
            return true;
        else
            return false;
    }

    /**
     * Return sub product
     */
    public function getSubProductId()
    {
        $salesOrderItem = $this->getrp_orderitem_id();

        $collection = mage::getModel('sales/order_item')
            ->getCollection()
            ->addFieldToFilter('parent_item_id', $salesOrderItem);
        foreach ($collection as $subProduct) {
            return $subProduct->getproduct_id();
        }

        throw new Exception('Unable to find sub product !');
    }

    /**
     * Return stock movement identificator
     *
     * @return unknown
     */
    protected function getSmUi()
    {
        return 'rma_item_' . $this->getId();
    }

    /**
     * Return associated RMA
     *
     */
    public function getRma()
    {
        if ($this->_rma == null) {
            $rmaId      = $this->getrp_rma_id();
            $this->_rma = mage::getModel('ProductReturn/Rma')->load($rmaId);
        }

        return $this->_rma;
    }
    
    /**
     * Return order item matching to the current Rma / Product
     * @return type
     */
    public function getOrderItem()
    {
        die('ok');
        return Mage::getModel('sales/order_item')->load($this->getrp_orderitem_id());
    }

    public function _beforeSave(){

        if(strtolower($this->getrp_request_type()) == 'refund' && $this->getRma()->productReservedForRma($this->getrp_product_id())){

            $this->getRma()->releaseProduct($this->getrp_product_id(), $this->getrp_qty());

        }

        return parent::_beforeSave();

    }

}