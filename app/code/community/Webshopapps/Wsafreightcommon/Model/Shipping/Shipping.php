<?php
/* YRC Freight Shipping
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsafreightcommon
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Wsafreightcommon_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo     make it ordered
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $limitCarrier = Mage::helper('wsafreightcommon')->limitCarriersBasedOnFreightRules($request,
                                                                                           $request->getLimitCarrier());

        $request->setLimitCarrier($limitCarrier);

     	return parent::collectRates($request);
    }

    /**
	 * Overrides this method in core, and decides which extension to call
	 * Uses a hierarchy to decide on best extension
	 * @see app/code/core/Mage/Shipping/Model/Mage_Shipping_Model_Shipping::collectCarrierRates()
	 */
 	public function collectCarrierRates($carrierCode, $request)
 	{
 		// check to see if handling Product enabled
	 	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct','shipping/handlingproduct/active')) {
			if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
		 		return parent::collectCarrierRates($carrierCode,$request);

		 	} else {
		 		if (!Mage::registry('handlingproduct_shipmodel')) {
					$model = Mage::getModel('handlingproduct/shipping_shipping');
					Mage::register('handlingproduct_shipmodel', $model);
				}
				$model = Mage::registry('handlingproduct_shipmodel') ;
				$model->collectCarrierRates($carrierCode, $request);
				$this->_result=$model->getResult();
				return $model;
		 	}
		}

 		if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingmatrix','shipping/handlingmatrix/active')) {
			if (!Mage::registry('handlingmatrix_shipmodel')) {
				$model = Mage::getModel('handlingmatrix/shipping_shipping');
				Mage::register('handlingmatrix_shipmodel', $model);
			}
			$model = Mage::registry('handlingmatrix_shipmodel');
			$model->collectCarrierRates($carrierCode, $request);
			$this->_result=$model->getResult();
			return $model;
		}

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance','shipping/insurance/active')) {
     		if (!Mage::registry('insurance_shipmodel')) {
				$model = Mage::getModel('insurance/shipping_shipping');
				Mage::register('insurance_shipmodel', $model);
			}
			$model = Mage::registry('insurance_shipmodel');
			$model->collectCarrierRates($carrierCode, $request);
			$this->_result=$model->getResult();
			return $model;
         }

	 	// default
	 	return parent::collectCarrierRates($carrierCode,$request);

	 }
}