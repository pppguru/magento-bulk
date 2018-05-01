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
class MDN_SalesOrderPlanning_Helper_Product extends Mage_Core_Helper_Abstract {

    public function getSimpleProductIds($limit = 0) {

        $select = mage::getResourceModel('catalog/product')
                        ->getReadConnection()
                        ->select()
                        ->from(mage::getResourceModel('catalog/product')->getTable('catalog/product'))
                        ->order('entity_id ASC')
                        ->where("type_id = 'simple'");
        if($limit>0){
            $select->limit($limit);
        }

        $productIds = mage::getResourceModel('catalog/product')->getReadConnection()->fetchCol($select);

        return $productIds;
    }

}