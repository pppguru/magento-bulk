<?php
class Wholesale_ThankyouWholesaleSignup_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
        $registered = Mage::getSingleton('core/session')->getThankyouWholesaleSignup();
        if (isset($registered) && $registered == 'sent') {
            Mage::getSingleton('core/session')->unsThankyouWholesaleSignup();
            $this->loadLayout();   
            $this->getLayout()->getBlock("head")->setTitle($this->__("Thank you for wholesale sign up!"));

            $this->renderLayout(); 
        } else {
            $this->_redirectUrl(Mage::getBaseUrl());
        }
    }
}