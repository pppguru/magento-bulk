<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Block_Checkout_Onepage_Shipping_Method_Additional
    extends Mage_Core_Block_Template
{
    protected $_template = 'rpsc/checkout/onepage/additional.phtml';
    
    protected $_nonShippableProducts = array();
    
    /**
     * Get list of non shippable products in current cart
     * 
     * @return array
     */
    public function getNonShippableProducts()
    {        
        if(empty($this->_nonShippableProducts)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $helper = $this->_getHelper();
            foreach($quote->getAllItems() as $item) {
                $product = $item->getProduct();
                if(!$helper->canSendToCountry($product, $quote)) {
                    $this->_nonShippableProducts[] = $product;
                }
            }
        }
        return $this->_nonShippableProducts;
    }

    /**
     *
     * @return Conlabz_Rpsc_Helper_Data
     */
    protected function _getHelper()
    {
        return $this->helper('rpsc');
    }
    
    /**
     * Check if cart has non shippable products
     * 
     * @return bool
     */
    public function hasNonShippableProducts()
    {
        return count($this->getNonShippableProducts()) > 0;
    }
}
