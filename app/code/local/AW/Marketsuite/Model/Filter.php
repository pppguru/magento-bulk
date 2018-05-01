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


class AW_Marketsuite_Model_Filter extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketsuite/filter');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('marketsuite/rule_condition_combine');
    }

    /**
     * Return all MSS rules as collection
     *
     * @return AW_Marketsuite_Model_Resource_Filter_Collection
     */
    public function getActiveRuleCollection()
    {
        return $this->getCollection()
            ->addIsActiveFilter()
        ;
    }

    /**
     * Return customers collection which satisfy MSS rule requirements
     *
     * @return Mage_Customer_Model_Entity_Customer_Collection
     */
    public function exportCustomers()
    {
        $customerCollection = Mage::getResourceModel('customer/customer_collection');
        $customerIndexTable = $this->getResource()->getTable('marketsuite/index_customer');
        $customerCollection->getSelect()
            ->join(
                array('index_customer' => $customerIndexTable),
                'e.entity_id = index_customer.customer_id',
                'rule_id'
            )
            ->where('index_customer.rule_id = ?', $this->getId())
        ;
        return $customerCollection;
    }

    /**
     * Return customers count which satisfy MSS rule requirements
     *
     * @return int
     */
    public function getMatchedCustomerCount()
    {
        return $this->exportCustomers()->getSize();
    }

    /**
     * Return orders collection which satisfy MSS rule requirements
     *
     * @return Mage_Sales_Model_Resource_Order_Collection|null
     */
    public function exportOrders()
    {
        $orderCollection = Mage::getResourceModel('sales/order_grid_collection');
        $orderIndexTable = $this->getResource()->getTable('marketsuite/index_order');
        $orderTable = $this->getResource()->getTable('sales/order');
        $orderCollection->getSelect()
            ->join(
                array('index_order' => $orderIndexTable),
                'main_table.entity_id = index_order.order_id',
                'rule_id'
            )
            ->join(
                array('sales_order' => $orderTable),
                'main_table.entity_id = sales_order.entity_id',
                'customer_email'
            )
            ->where('index_order.rule_id = ?', $this->getId())
        ;
        return $orderCollection;
    }

    /**
     * Return orders count which satisfy MSS rule requirements
     *
     * @return int
     */
    public function getOrderCount()
    {
        return $this->exportOrders()->getSize();
    }

    public function getQuery()
    {
        return $this->getConditions()->getQuery();
    }

    /**
     * Checking for reindex process is running
     *
     * @return bool
     */
    public function isReindexRunning()
    {
        if (is_null($this->getData('progress_percent'))) {
            return false;
        }

        if ((int)$this->getData('progress_percent') === 100) {
            return false;
        }

        return true;
    }

    public function getValidatedOrdersIds($orderCollection)
    {
        return $this->getConditions()->getValidatedOrdersIds($orderCollection);
    }

    public function getValidatedCustomersIds($customerCollection)
    {
        return $this->getConditions()->getValidatedCustomersIds($customerCollection);
    }
}