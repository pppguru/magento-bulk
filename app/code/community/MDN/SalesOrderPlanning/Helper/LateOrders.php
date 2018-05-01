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
class MDN_SalesOrderPlanning_Helper_LateOrders extends Mage_Core_Helper_Abstract {

    private $_lateOrders = null;

    /**
     * Return late orders
     *
     * @return unknown
     */
    public function getCollection() {
        if ($this->_lateOrders == null) {
            $today = date('Y-m-d');
            if (mage::helper('AdvancedStock/FlatOrder')->ordersUseEavModel()) {
                $this->_lateOrders = mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('anounced_date', array('neq' => '0000-00-00'))
                        ->addAttributeToFilter('state', array('in' => array('new', 'pending', 'processing')))
                        ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                        ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                        ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
                        ->addExpressionAttributeToSelect('shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}}, " (", {{shipping_company}}, ")")', array('shipping_firstname', 'shipping_lastname', 'shipping_company'))
                        ->joinTable(mage::getModel('Purchase/Constant')->getTablePrefix() . 'purchase_sales_order_planning', 'psop_order_id=entity_id', array(
                            'psop_delivery_date' => 'psop_delivery_date',
                            'psop_delivery_date_force' => 'psop_delivery_date_force'
                                )
                        )
                        ->addExpressionAttributeToSelect('diff', 'DATEDIFF(psop_delivery_date, {{anounced_date}})', array('anounced_date'))
                        ->addAttributeToFilter('diff', array('gt' => '0'));
            } else {
                $this->_lateOrders = mage::getModel('sales/order')
                    ->getCollection()
                    ->addFieldToFilter('state', array('in' => array('new', 'pending', 'processing')))
                    ->join('SalesOrderPlanning/Planning', 'psop_order_id=entity_id '
                        . ' AND ( '
                        . '(DATEDIFF(psop_delivery_date, anounced_date) > 0) '
                        . ' OR (psop_delivery_date < DATE(NOW())) '
                        . ')')
                    ->join('sales/order_address', '`sales/order_address`.entity_id=shipping_address_id', array('shipping_name' => "concat(firstname, ' ', lastname)")) ;
            }
        }
        return $this->_lateOrders;
    }

}