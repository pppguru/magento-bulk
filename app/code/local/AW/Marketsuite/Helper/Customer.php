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


class AW_Marketsuite_Helper_Customer extends Mage_Core_Helper_Data
{
    public function getCustomerCount()
    {
        return Mage::getResourceModel('customer/customer_collection')->getSize();
    }

    public function getUpdatedCustomerIdList($date)
    {
        return Mage::getResourceModel('customer/customer_collection')
            ->addFieldToFilter(
                'updated_at', array('gteq' => $date)
            )
            ->getAllIds()
        ;
    }

    /**
     * @param int|Mage_Customer_Model_Customer $customer
     *
     * @return array
     */
    public function getOrderIdListByCustomer($customer)
    {
        $orderCollection = $this->getOrderCollectionByCustomer($customer);

        // Fix for 1.4.1.1
        $idsSelect = clone $orderCollection->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns($orderCollection->getResource()->getIdFieldName(), 'main_table');

        return $orderCollection->getConnection()->fetchCol($idsSelect);
    }

    /**
     * @param int|Mage_Customer_Model_Customer $customer
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrderCollectionByCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = intval($customer->getId());
        }
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->addFieldToFilter('customer_id', array('eq' => $customer));
        return $orderCollection;
    }

    public function getOrderCollectionByCustomerIds(array $customerIds)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array($orderCollection->getResource()->getIdFieldName(), 'customer_id'), 'main_table')
            ->where('customer_id IN(?)', $customerIds)
        ;
        return $orderCollection;
    }

    /**
     * Getting product list viewed by customer
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return array
     */
    public function getProductListViewedByCustomer($customer)
    {
        $viewedCollection = Mage::getResourceModel('reports/event_collection');
        $viewedCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('object_id')
            ->where('subtype = ?', 0)
            ->where('subject_id = ?', $customer->getId())
        ;

        $productIds = array();
        foreach ($viewedCollection as $viewedProduct) {
            $productIds[] = $viewedProduct->getData('object_id');
        }

        $viewsCountArray = array_count_values($productIds);

        $products = array();
        foreach ($viewsCountArray as $productId => $viewsCount) {
            $products[] = Mage::getModel('catalog/product')->load($productId)->setViewsCount($viewsCount);
        }
        return $products;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getShoppingCartByCustomer($customer)
    {
        return Mage::getModel('sales/quote')
            ->setSharedStoreIds(array_keys(Mage::app()->getStores()))
            ->loadByCustomer($customer)
        ;
    }

    public function getWishlistByCustomer($customer)
    {
        return Mage::getModel('wishlist/wishlist')->loadByCustomer($customer);
    }

    /**
     * Adding subscription information from Mage_Newsletter to customer
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function addNativeNewsletterData($customer)
    {
        if (!$customer instanceof Mage_Customer_Model_Customer) {
            return $customer;
        }

        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        if (!$subscriber->isSubscribed()) {
            $customer->setData('newslettersubscription', 0);
        } else {
            $customer->setData('newslettersubscription', 1);
        }
        return $customer;
    }

    /**
     * Adding subscription information from AW_Advancednewsletter to customer
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function addAdvancedNewsletterData($customer)
    {
        if (
            !$customer instanceof Mage_Customer_Model_Customer
            || !Mage::helper('marketsuite')->isAdvancedNewsletterEnabled()
        ) {
            return $customer;
        }

        $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByCustomer($customer);
        if (!$subscriber->getId()) {
            $customer->setData('annewslettersubscription', array());
        } else {
            $customer->setData('annewslettersubscription', $subscriber->getSegmentsCodes());
        }

        return $customer;
    }

    public function getAllIds(Zend_Db_Select $select)
    {
        $customerCollection = Mage::getResourceModel('customer/customer_collection');
        $idsSelect = clone $select;
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns($customerCollection->getResource()->getIdFieldName(), 'e');
        return $customerCollection->getConnection()->fetchCol($idsSelect);
    }
}