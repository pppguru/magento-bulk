<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Model_Resource_Filter_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketsuite/filter');
    }

    protected function _toOptionArray($valueField = 'filter_id', $labelField = 'name', $additional = array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    protected function _toOptionHash($valueField = 'filter_id', $labelField = 'name')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    public function addIsActiveFilter()
    {
        $this->addFieldToFilter('is_active', array('eq' => 1));
        return $this;
    }

    public function addSortByName()
    {
        $this->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC);
        return $this;
    }

    public function addOrderCount()
    {
        $select = new Zend_Db_Select($connection = Mage::getSingleton('core/resource')->getConnection('read'));
        $select
            ->from(
                Mage::getSingleton('core/resource')->getTableName('marketsuite/index_order'),
                array('rule_id', 'order_count' => new Zend_Db_Expr("COUNT(order_id)"))
            )
            ->group('rule_id')
        ;

        $this
            ->getSelect()
            ->joinLeft(
                array('order_idx' => $select),
                'main_table.filter_id = order_idx.rule_id',
                'order_count'
            )
        ;
        return $this;
    }

    public function addCustomerCount()
    {
        $select = new Zend_Db_Select($connection = Mage::getSingleton('core/resource')->getConnection('read'));
        $select
            ->from(
                Mage::getSingleton('core/resource')->getTableName('marketsuite/index_customer'),
                array('rule_id', 'customer_count' => new Zend_Db_Expr("COUNT(customer_id)"))
            )
            ->group('rule_id')
        ;

        $this
            ->getSelect()
            ->joinLeft(
                array('customer_idx' => $select),
                'main_table.filter_id = customer_idx.rule_id',
                'customer_count'
            )
        ;
        return $this;
    }
}