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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_CustomerOrders extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_hdu3/ticket/edit/container/customer_orders.phtml');
    }

    /**
     * @return bool
     */
    public function isCanShow()
    {
        $currentDepartmentAgent = Mage::helper('aw_hdu3/ticket')->getCurrentDepartmentAgent();
        if (AW_Helpdesk3_Helper_Config::isCanShowCustomerOrdersForAllAgents()) {
            return true;
        } else if (AW_Helpdesk3_Helper_Config::isCanShowCustomerOrdersForPrimaryAgentOnly()
            && $currentDepartmentAgent->getId() === $this->getTicket()->getDepartment()->getPrimaryAgentId()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return int
     */
    public function getTotalOrderCount()
    {
        return $this->getOrderCollection()->getSize();
    }

    /**
     * @return int
     */
    public function getCompletedOrderCount()
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = $this->getCustomerOrderCollection();
        $collection
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
        ;
        return $collection->getSize();
    }

    /**
     * @return float
     */
    public function getTotalInvoicedSummary()
    {
        $collection = $this->getCustomerOrderCollection();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->addExpressionFieldToSelect(
            'total_paid',
            'SUM({{base_total_paid}})',
            'base_total_paid'
        );
        $totalPaidArray = $collection->getColumnValues('total_paid');
        return (float)$totalPaidArray[0];
    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrderCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = $this->getCustomerOrderCollection();
        $collection->getSelect()->limit(5);
        $collection->addOrder('entity_id', Varien_Data_Collection_Db::SORT_ORDER_DESC);
        return $collection;
    }

    /**
     * @param float $price
     *
     * @return string
     */
    public function formatBaseCurrency($price)
    {
        return Mage::app()->getWebsite()->getBaseCurrency()->format($price);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function isOrderComplete(Mage_Sales_Model_Order $order)
    {
        return $order->getState() === Mage_Sales_Model_Order::STATE_COMPLETE;
    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getCustomerOrderCollection()
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToFilter('customer_email', $this->getTicket()->getCustomerEmail())
        ;
        return $collection;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return string
     */
    public function getFrontendProductUrlForOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $productId = $orderItem->getProductId();
        if ($orderItem->getParentItemId()) {
            $parentItem = Mage::getModel('sales/order_item')->load($orderItem->getParentItemId());
            $productId = $parentItem->getProductId();
        }
        $product = Mage::getModel('catalog/product')->setStoreId($orderItem->getStoreId())->load($productId);
        if (null === $product->getId()) {
            return '#';
        }
        return $product->getProductUrl();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return string
     */
    public function getFrontendProductBasePrice(Mage_Sales_Model_Order_Item $orderItem)
    {
        $itemBasePrice = $orderItem->getBasePrice();
        if ($orderItem->getParentItemId()) {
            $parentItem = Mage::getModel('sales/order_item')->load($orderItem->getParentItemId());
            if ($parentItem->getProductType() != 'bundle') {
                $itemBasePrice = $parentItem->getBasePrice();
            }
        }
        return $orderItem->getOrder()->getBaseCurrency()->format($itemBasePrice);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return string
     */
    public function getFrontendProductPrice(Mage_Sales_Model_Order_Item $orderItem)
    {
        $itemPrice = $orderItem->getPrice();
        if ($orderItem->getParentItemId()) {
            $parentItem = Mage::getModel('sales/order_item')->load($orderItem->getParentItemId());
            if ($parentItem->getProductType() != 'bundle') {
                $itemPrice = $parentItem->getPrice();
            }
        }
        return $orderItem->getOrder()->getOrderCurrency()->format($itemPrice);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return bool
     */
    public function isCanRenderOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $notAllowedProductTypes = array(
            Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE,
            'bundle'
        );
        if (in_array($orderItem->getProductType(), $notAllowedProductTypes)) {
            return false;
        }
        return true;
    }
}