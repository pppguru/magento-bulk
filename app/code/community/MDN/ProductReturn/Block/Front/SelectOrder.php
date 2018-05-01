<?php

class MDN_ProductReturn_Block_Front_SelectOrder extends Mage_Core_Block_Template
{
    private $_list = null;

    /**
     * Return orders available for product return
     *
     */
    public function getList()
    {
        if ($this->_list == null) {
            $this->_list = mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('customer_id', $this->getCustomer()->getId());
        }

        return $this->_list;
    }

    /**
     * return current customer
     *
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Return url to select an order to request product return
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getSelectUrl($order)
    {
        return $this->getUrl('ProductReturn/Front/NewRequest', array('order_id' => $order->getId()));
    }

    /**
     * Check if it is possible to request for product return on order depending of the date of the last shipment
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return unknown
     */
    public function IsOrderAvailable($order)
    {

        return mage::helper('ProductReturn')->IsOrderAvailable($order);

    }
}