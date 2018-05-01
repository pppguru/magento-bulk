<?php
class Bulksupplements_CustomShipRate_Model_Carrier_Customshiprate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'customshiprate';
    protected $_isFixed = false;

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	if(!Mage::app()->getStore()->isAdmin()) {
    		return false;		// Only allow this to be used from the admin system
    	}
    	
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $result = Mage::getModel('shipping/rate_result');

		$shippingPrice = Mage::getSingleton('core/session')->getCustomshiprateAmount();
		$baseShippingPrice = Mage::getSingleton('core/session')->getCustomshiprateBaseAmount();
		$description = Mage::getSingleton('core/session')->getCustomshiprateDescription();
        
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('customshiprate');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('customshiprate');
            $method->setMethodTitle((strlen($description) > 0) ? $description : $this->getConfigData('name'));

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }
        
        return $result;
    }

    public function getAllowedMethods()
    {
        return array('customshiprate'=>$this->getConfigData('name'));
    }

}
