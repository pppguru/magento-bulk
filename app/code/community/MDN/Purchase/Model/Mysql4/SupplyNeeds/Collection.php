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

/**
 * Collection de quotation
 *
 */
class MDN_Purchase_Model_Mysql4_SupplyNeeds_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/SupplyNeeds'); //VIEW erp_view_supplyneeds_global
    }

    /**
     * Return ids for suppliers used in supply needs
     */
    public function getSupplierIds() {

        $productSupplierTable = Mage::getSingleton('core/resource')->getTableName('Purchase/ProductSupplier');

        $this->getSelect()
                ->reset()
                ->from($this->getMainTable(), '')
                ->from($productSupplierTable, 'pps_supplier_num')
                ->where('pps_product_id = product_id')
        ;

        $supplierIds = $this->getConnection()->fetchCol($this->getSelect());
        $supplierIds = array_unique($supplierIds);

        return $supplierIds;
    }

    /**
     * Return amount for one supplier / one status
     */
    public function getAmount($supplierId, $status, $mode) {

        //echo $supplierId.' - '.$status.' - '.$mode;
        
        $productSupplierTable = Mage::getSingleton('core/resource')->getTableName('Purchase/ProductSupplier');
        $this->getSelect()
                ->reset()
                ->from($this->getMainTable(), 'sum(' . $mode . ' * pps_last_unit_price)')
                ->from($productSupplierTable, '')
                ->where('pps_product_id = product_id')
                ->where('pps_supplier_num = ' . $supplierId)
        ;
        if ($status)
            $this->getSelect()->where("status = '".$status."'");

        $value = $this->getConnection()->fetchOne($this->getSelect());
        return number_format($value, 0, '', '');
    }

}