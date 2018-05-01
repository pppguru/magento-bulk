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
class MDN_Purchase_Model_Mysql4_ProductSupplier extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('Purchase/ProductSupplier', 'pps_num');
    }

    public function getProductIdForSupplierSku($supplierSku, $supplierId) {
        $select = $this->getReadConnection()->select()
                        ->reset()
                        ->from($this->getMainTable(), 'pps_product_id')
                        ->where('pps_reference=?', $supplierSku)
                        ->where('pps_supplier_num=?', $supplierId);
        $productId = $this->getReadConnection()->fetchOne($select);
        return $productId;
    }

    public function getSupplierSku($productId, $supplierId) {
        $select = $this->getReadConnection()->select()
                        ->reset()
                        ->from($this->getMainTable(), 'pps_reference')
                        ->where('pps_product_id=?', $productId)
                        ->where('pps_supplier_num=?', $supplierId);
        $productId = $this->getReadConnection()->fetchOne($select);
        return $productId;
    }

    public function getAverageCost($productId) {
        $select = $this->getReadConnection()->select()
                        ->reset()
                        ->from($this->getMainTable(), 'avg(pps_last_price)')
                        ->where('pps_product_id=?', $productId)
                        ->where('pps_last_price>0');
        $cost = $this->getReadConnection()->fetchOne($select);
        return $cost;
    }

    public function getProductIdsForSupplier($supplierId)
    {
        $select = $this->getReadConnection()->select()
                        ->reset()
                        ->from($this->getMainTable(), 'pps_product_id')
                        ->where('pps_supplier_num=?', $supplierId);
        $productIds = $this->getReadConnection()->fetchCol($select);
        return $productIds;
    }

}

?>