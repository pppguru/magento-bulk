<?php

class MDN_ProductReturn_Model_Guest
{

    /**
     *
     * @param type $customer
     * @param type $order
     *
     * @return \type
     */
    public function associateOrderToCustomer($customer, $order)
    {
        $oldCustomerName = $order->getCustomerName();

        //edit order
        $order->setcustomer_id($customer->getId())->setcustomer_is_guest(0);

        //change customer name & email in order
        $order->setcustomer_email($customer->getEmail());
        $order->setcustomer_firstname($customer->getFirstname());
        $order->setcustomer_lastname($customer->getLastname());
        $order->setcustomer_middlename($customer->getMiddlename());

        //add comment in order
        $msg = Mage::helper('ProductReturn')->__('Customer changed from "%s" to "%s"', $oldCustomerName, $customer->getName());
        $order->addStatusHistoryComment($msg);

        $order->save();

        // find the associated rma for this order
        $RmaCollection = Mage::getModel('ProductReturn/Rma')->loadByOrder($order->getId());
        
        foreach($RmaCollection as $rma){
                $rma->setrma_customer_id($customer->getId());
                $rma->save();
        }
        
        // find the other RMA for the customer
        
        //edit sales flat order grid (not necessary, handled by magento events)
        //$sfo = Mage::getModel('sales/order_grid')->load($order->getId());
        //$sfo->setcustomer_id($customer->getId())->save();

        return $order;
    }

    /**
     * Check if email is already used for an account
     *
     * @param type $email
     * @param      $websiteId
     *
     * @return bool
     */
    protected function emailExists($email, $websiteId)
    {
        $collection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('email', $email);
        if (Mage::getModel('customer/customer')->getSharingConfig()->isWebsiteScope())
            $collection->addFieldToFilter('website_id', $websiteId);

        return ($collection->getSize() > 0);
    }

    /**
     *
     * @param type $email
     * @param type $orderId
     *
     * @return null
     */
    protected function findOrder($email, $orderId)
    {
        $order = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('increment_id', $orderId)
            ->addFieldToFilter('customer_email', $email)
            ->getFirstItem();
        if (!$order->getId())
            return null;
        else
            return $order;

    }

}