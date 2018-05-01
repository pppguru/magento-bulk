<?php
class  Idev_OneStepCheckout_Model_Sales_Quote extends Mage_Sales_Model_Quote
{

    /**
     * Collect totals patched for magento issue #26145
     *
     * @return Mage_Sales_Model_Quote
     */
    public function collectTotals()
    {

        /**
         * patch for magento issue #26145
         */
        if (!$this->getTotalsCollectedFlag()) {

            $items = $this->getAllItems();

            foreach($items as $item){
                $item->setData('calculation_price', null);
                $item->setData('original_price', null);
            }

        }

        parent::collectTotals();
        return $this;

    }

    /**
     * Check is allow Guest Checkout
     *
     * @deprecated after 1.4 beta1 it is checkout module responsibility
     * @return bool
     */
    public function isAllowedGuestCheckout()
    {
        $persistentHelper  = Mage::helper('onestepcheckout')->getPersistentHelper();
        if(is_object($persistentHelper)){
            //persistant checkout disables guest checkout
            if($persistentHelper->isPersistent()){
                return true;
            } else {
                return parent::isAllowedGuestCheckout();
            }
        }
    }
}
