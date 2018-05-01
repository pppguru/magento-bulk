<?php

class Wholesale_Customer_Block_Form_Register extends Mage_Customer_Block_Form_Register {
	/**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getWholesalePostActionUrl()
    {
        return $this->helper('customer')->getRegisterWholesalePostUrl();
    }
}