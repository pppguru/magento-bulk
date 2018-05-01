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
class MDN_AdvancedStock_Helper_Sales_PendingOrders extends Mage_Core_Helper_Abstract {

    /**
     * Return pending order ids for one product
     *
     * @param unknown_type $productId
     */
    public function getPendingOrderIdsForProduct($productId) {

        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = "
                select
                        entity_id
                from
                        " . $prefix . "sales_flat_order_item tbl_order_item,
                        " . $prefix . "sales_flat_order tbl_order
                where
                        tbl_order.entity_id = tbl_order_item.order_id
                        and product_id = " . $productId . "
                        and tbl_order.state not in ('complete', 'canceled', 'closed')
                ";

        $orderIds = array();
        $orderIds = mage::getResourceModel('cataloginventory/stock_item_collection')->getConnection()->fetchCol($sql);

        return $orderIds;
    }

}