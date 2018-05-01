<?php

class MDN_ProductReturn_GuestController extends Mage_Core_Controller_Front_Action {

    /**
     * Display form to create the customer account
     */
    public function FormAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create customer account
     */
    public function SubmitAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $post = $this->getRequest()->getPost();

        try {

            //check if client already exists
            $websiteId = Mage::app()->getWebsite()->getId();
            $customer = $this->_customerExists($post['email'], $websiteId);
            if ($customer)
            {
                Mage::getSingleton('core/session')->addError($this->__('Dear customer, your login name already exists. Please login here'));
                $this->_redirect('customer/account/login');
                return;
            }

            //create and log the customer
            $customer = Mage::getModel('customer/customer')->setId(null);
            foreach ($post as $k => $v)
                $customer->setData($k, $v);
            $customer->getGroupId();
            $customer->save();
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

            //assign order to customer
            $order = Mage::getModel('sales/order')->load($orderId);
            Mage::getSingleton('ProductReturn/Guest')->associateOrderToCustomer($customer, $order);

            //redirect in new RMA request form
            Mage::getSingleton('customer/session')->addSuccess($this->__('Your account has been created'));
            $this->_redirect('ProductReturn/Front/NewRequest', array('order_id' => $orderId));
            
        } catch (Exception $ex) {
            Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
            $this->_redirect('ProductReturn/Guest/Form', array('order_id' => $orderId));
        }
        
    }

    protected function _customerExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

}
