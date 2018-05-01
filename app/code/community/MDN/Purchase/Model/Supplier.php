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
class MDN_Purchase_Model_Supplier extends Mage_Core_Model_Abstract {

    private $_taxRate = null;

    /**
     * Constructor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/Supplier');
    }

    /**
     * Return formated supplier address
     * 
     */
    public function getAddressAsText($ShowAll = true) {
        $retour = $this->getsup_name() . " \n ";

        $retour .= $this->getsup_address1() . " \n ";
        $retour .= $this->getsup_address2() . " \n ";

        $retour .= $this->getsup_zipcode() . ' ' . $this->getsup_city() . " \n ";
        $retour .= $this->getsup_state() . " ";
        if ($this->getsup_country() != ''){
            $retour .= ' '. Mage::getModel('directory/country')->loadByCode($this->getsup_country())->getName();
        }
        $retour .= " \n ";
        
        if ($ShowAll) {
            $retour .= 'Fax : ' . $this->getsup_fax() . " \n ";
            $retour .= 'Email : ' . $this->getsup_mail();
        }
        return $retour;
    }

    /**
     * getproduct reference (sku) for this supplier
     *
     * @param unknown_type $ProductId
     */
    public function getProductReference($ProductId) {
        $retour = '';

        $collection = mage::getModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->addFieldToFilter('pps_product_id', $ProductId)
                        ->addFieldToFilter('pps_supplier_num', $this->getId());

        if (sizeof($collection) > 0) {
            foreach ($collection as $item) {
                $retour = $item->getpps_reference();
            }
        }

        return $retour;
    }

    /**
     * getproduct name for this supplier
     *
     * @param unknown_type $ProductId
     */
    public function getProductName($ProductId) {
        $retour = '';

        $collection = mage::getModel('Purchase/ProductSupplier')
            ->getCollection()
            ->addFieldToFilter('pps_product_id', $ProductId)
            ->addFieldToFilter('pps_supplier_num', $this->getId());

        if (sizeof($collection) > 0) {
            foreach ($collection as $item) {
                $retour = $item->getpps_product_name();
            }
        }

        return $retour;
    }

    /**
     * Return linked products
     *
     */
    public function getProducts() {
        $collection = Mage::getResourceModel('catalog/product_collection')
                        ->joinField('stock',
                                'Purchase/ProductSupplier',
                                'pps_supplier_num',
                                'pps_product_id=entity_id',
                                'pps_supplier_num=' . $this->getId(),
                                'inner');
        return $collection;
    }


    /**
     * Supplier aftersave
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        Mage::dispatchEvent('purchase_supplier_aftersave', array('supplier' => $this));
    }

    /**
     * Return discount level for one product
     */
    public function getProductDiscountLevel($productId, $qty = 1) {
        //supplier permanent discount
        $supplierDiscountLevel = $this->getsup_discount_level();

        //try to add discount for this product
        $productSupplier = mage::getModel('Purchase/ProductSupplier')->getProductForSupplier($productId, $this->getId());
        if ($productSupplier) {
            $result = 100 * (1 - $supplierDiscountLevel / 100);
            $productDiscountLevel = $productSupplier->getpps_discount_level();
            $result = $result * (1 - $productDiscountLevel / 100);
            return 100 - $result;
        }
        else
            return $supplierDiscountLevel;
    }

    /**
     * Return tax rate object
     * @return <type>
     */
    public function getTaxRate()
    {
        if ($this->_taxRate == null)
        {
            $taxRateId = $this->getsup_tax_rate();
            $this->_taxRate = Mage::getModel('Purchase/TaxRates')->load($taxRateId);
        }
        return $this->_taxRate;
    }

    public function getPos()
    {
        return Mage::getModel('Purchase/Order')->getCollection()->addFieldToFilter('po_sup_num', $this->getId());
    }

    public function getPoTotal()
    {
        $total = 0;

        foreach($this->getPos() as $po)
        {
            $total += $po->getTotalHt();
        }

        return $total;
    }
}