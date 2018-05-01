<?php

class MDN_ProductReturn_Block_Front_List extends Mage_Core_Block_Template
{
    private $_list = null;

    /**
     * Return product return list for current customer
     *
     * @return unknown
     */
    public function getList()
    {
        if ($this->_list == null) {
            $this->_list = mage::getModel('ProductReturn/Rma')
                ->getCollection()
                ->addFieldToFilter('rma_customer_id', $this->getCustomer()->getId())
                ->setOrder('rma_id', 'DESC');
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
     * Return if customer can request a product return
     *
     */
    public function canRequestForProductReturn()
    {
        return mage::getStoreConfig('productreturn/general/allow_customer_request');
    }

    /**
     * return url to request a product return
     *
     */
    public function getProductReturnRequestUrl()
    {
        return $this->getUrl('ProductReturn/Front/NewRequestSelectOrder');
    }

    public function getViewUrl($productReturn)
    {
        return $this->getUrl('ProductReturn/Front/View', array('rma_id' => $productReturn->getId()));
    }
}
