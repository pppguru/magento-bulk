<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_Observer
{
    /**
     * flag whether the info has already been injected
     *
     * @var bool
     */
    protected $_infoInjected = false;

    /**
     *
     * @var string
     */
    protected $_infoBlockHtml;

    /**
     * Removes items if not shippable to selected country
     * 
     * @param object $observer
     */
    public function removeItemsFromCart(Varien_Event_Observer $observer)
    {
        $quote   = Mage::getModel('checkout/cart')->getQuote();
        $helper  = $this->_getHelper();

        foreach($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if(!$helper->canSendToCountry($product, $quote)) {
                $quote->removeItem($item->getId());
            }
        }
        $quote->save();
    }

    /**
     *
     * @return Conlabz_Rpsc_Helper_Data
     */
    protected function _getHelper()
    {
        return  Mage::helper('rpsc');
    }
    
    /**
     * Adds warning to checkout
     *
     * @param Varien_Event_Observer $observer
     */
    public function addRemoveItemsInfo(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        // inject to shipping method if not virtual
        if ($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Additional &&
            !$this->_getQuote()->isVirtual()
        ) {
            $this->_injectBlock($observer);
        }

        // inject to payment methods if quote is virtual
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods &&
            $this->_getQuote()->isVirtual() &&
            Mage::app()->getRequest()->isXmlHttpRequest()
        ) {
            $this->_injectBlock($observer);
        }
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    protected function _injectBlock(Varien_Event_Observer $observer)
    {
        if (!$this->_infoInjected) {
            $block = $observer->getBlock();
            $transport = $observer->getTransport();
            $html      = $transport->getHtml();
            $infoBlock = $this->_getInfoBlockHtml($block);
            $transport->setHtml($infoBlock . $html);
            $this->_infoInjected = true;
        }
    }

    /**
     *
     * @param Mage_Core_Block_Abstract $block
     * @return string
     */
    protected function _getInfoBlockHtml(Mage_Core_Block_Abstract $block)
    {
        $infoBlock = $block->getLayout()
            ->createBlock(
                'rpsc/checkout_onepage_shipping_method_additional',
                'rpsc_shipping_additional'
            )
            ->toHtml();
        return $infoBlock;
    }

    /**
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
    }
}
