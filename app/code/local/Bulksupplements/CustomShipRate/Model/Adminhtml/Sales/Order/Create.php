<?php
class Bulksupplements_CustomShipRate_Model_Adminhtml_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Parse data retrieved from request
     *
     * @param   array $data
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } else {
            return $this;
        }

        if (isset($data['account'])) {
            $this->setAccountData($data['account']);
        }

        if (isset($data['comment'])) {
            $this->getQuote()->addData($data['comment']);
            if (empty($data['comment']['customer_note_notify'])) {
                $this->getQuote()->setCustomerNoteNotify(false);
            } else {
                $this->getQuote()->setCustomerNoteNotify(true);
            }
        }

        if (isset($data['billing_address'])) {
            $this->setBillingAddress($data['billing_address']);
        }

        if (isset($data['shipping_address'])) {
            $this->setShippingAddress($data['shipping_address']);
        }

        if (isset($data['shipping_method'])) {
            $this->setShippingMethod($data['shipping_method']);
        }

        if (isset($data['payment_method'])) {
            $this->setPaymentMethod($data['payment_method']);
        }
        
        $reinit_rates = false;
        
        if (isset($data['shipping_amount'])) {            	
            $shippingPrice = $this->_parseShippingPrice($data['shipping_amount']);
			Mage::getSingleton('core/session')->setCustomshiprateAmount($shippingPrice);
        	$reinit_rates = true;
        }

        if (isset($data['base_shipping_amount'])) {
            $baseShippingPrice = $this->_parseShippingPrice($data['base_shipping_amount']);
			Mage::getSingleton('core/session')->setCustomshiprateBaseAmount($baseShippingPrice);
        	$reinit_rates = true;
        }

        if (isset($data['shipping_description'])) {
			Mage::getSingleton('core/session')->setCustomshiprateDescription($data['shipping_description']);
        	$reinit_rates = true;
        }

        if (isset($data['coupon']['code'])) {
            $this->applyCoupon($data['coupon']['code']);
            $reinit_rates = true;
        }
        
        if($reinit_rates)
        {
        	//$this->collectShippingRates();
        	//$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        	//$this->collectRates();
        	//$this->getQuote()->collectTotals();
        }
        
        return $this;
    }

    protected function _parseShippingPrice($price)
    {
        $price = Mage::app()->getLocale()->getNumber($price);
        $price = $price>0 ? $price : 0;
        return $price;
    }

}
