<?php
/* YRC Freight Shipping
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsaupsfreight
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


abstract class Webshopapps_Wsafreightcommon_Model_Carrier_Abstract
    extends Webshopapps_Wsacommon_Model_Shipping_Carrier_Baseabstract
{
    const USA_COUNTRY_ID = 'US';
    const PUERTORICO_COUNTRY_ID = 'PR';
    const GUAM_COUNTRY_ID = 'GU';
    const GUAM_REGION_CODE = 'GU';

    public function isCityRequired()
    {
        return false;
    }

 	/**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
 	public function isZipCodeRequired($countryId = null) //Mage_Shipping_Model_Rate_Request $request = null TODO
    {
      /*  if ($request instanceof Mage_Shipping_Model_Rate_Request) {
            return !Mage::helper('directory')->isZipCodeOptional($request->getDestCountryId());
        }*/

    	if (!is_null($countryId)) {
            return !Mage::helper('directory')->isZipCodeOptional($countryId);
        }

        return true;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Carrier_Abstract|Mage_Shipping_Model_Rate_Result_Error|boolean
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        return true;
    }

    protected function getLineItems($ignoreFreeItems, $useParent=true, $getDimensional = false) {

        // override use of $useParent and get from freight common instead
        $useParent = Mage::getStoreConfig('shipping/wsafreightcommon/use_parent');

        $lineItemArray=array();
        $defaultFreightClass = Mage::helper('wsafreightcommon')->getDefaultFreightClass();

        foreach ($this->_request->getAllItems() as $item) {

            $weight=0;
            $qty=0;
            $price=0;

            if (!Mage::helper('wsacommon/shipping')->getItemTotals($item, $weight,$qty,$price,$useParent,$ignoreFreeItems)) {
                continue;
            }

            $product = Mage::helper('wsacommon/shipping')->getProduct($item,$useParent);

            $weight=ceil($weight);  // round up to nearest whole - required for conway
            $class=trim($product->getData('freight_class'));

            if (empty($class) || $class=='') {
                $class=$defaultFreightClass; // use default
            }

            if (empty($lineItemArray) || !array_key_exists($class,$lineItemArray)) {
                if ($getDimensional) {
                    $lineItemArray[$class]= array(
                        'weight' => $weight,
                        'items' => array($item),
                    );
                } else {
                    $lineItemArray[$class]= $weight;
                }
            } else {
                if ($getDimensional) {
                    $lineItemArray[$class]['weight'] += $weight;
                    $lineItemArray[$class]['items'][] = $item;
                } else {
                    $lineItemArray[$class]= $lineItemArray[$class] + $weight;
                }
            }

        }

        if ($getDimensional) {
            $this->_getBoxes($lineItemArray);
        }

        return $lineItemArray;  // Note: format of this is different if its dimensional or not
    }

    protected function _getBoxes(&$lineItemArray) {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
            foreach($lineItemArray as $key=>$lineItem ){
                //Get the boxes
                $boxes = Mage::getSingleton('shipusa/dimcalculate')->getBoxes($lineItem['items']);
                //Add the boxes to the $lineItemArray
                $lineItemArray[$key]['boxes'] = $boxes;
            }
        }
    }


    public function setBaseRequest(Mage_Shipping_Model_Rate_Request $request)
   {
    	$accArray=array();
        $quoteShippingAddress = null;
        $this->_topUpPrice=0;

    	$this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        } else {
            $r->setService('ALL');
        }

        $r->setAllowedMethods($this->getConfigData('allowed_methods'));
        $r->setChargeLiftgateOnly($request->getChargeLiftgateOnly());  // declared in shipping override

        $liftOrigin = Mage::getStoreConfigFlag('shipping/wsafreightcommon/liftgate_origin');
        $r->setOriginLiftgateReqd($liftOrigin); // deprecated do not use
        if ($liftOrigin) {
            $accArray[]='LIFT_ORIGIN';  // please switch to using this rather than above, its a lot easier!
        }

        $resOrigin = Mage::getStoreConfigFlag('shipping/wsafreightcommon/residential_origin');
        $r->setOriginResidential($resOrigin);  // deprecated do not use

        if ($resOrigin) {
        	$accArray[]='RES_ORIGIN';  // please switch to using this rather than above, its a lot easier!
        }


     	if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        }

        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());
        $r->setOrigCountryIso3(Mage::getModel('directory/country')->load($origCountry)->getIso3Code());

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = Mage::getStoreConfig('shipping/origin/region_id', $this->getStore());
            if (is_numeric($origRegionCode)) {
                $origRegionCode = Mage::getModel('directory/region')->load($origRegionCode)->getCode();
            }
        }
        $r->setOrigRegionCode($origRegionCode);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        }

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig('shipping/origin/city', $this->getStore()));
        }


        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        //for UPS, puero rico state for US will assume as puerto rico country
        if ($destCountry==self::USA_COUNTRY_ID && ($request->getDestPostcode()=='00912' || $request->getDestRegionCode()==self::PUERTORICO_COUNTRY_ID)) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($destCountry == self::USA_COUNTRY_ID && $request->getDestRegionCode() == self::GUAM_REGION_CODE) {
            $destCountry = self::GUAM_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());
        $r->setDestCountryIso3(Mage::getModel('directory/country')->load($destCountry)->getIso3Code());

        $r->setDestRegionCode($request->getDestRegionCode());
 		$r->setDestCity($request->getDestCity());

        if ($request->getDestPostcode()) {
            $r->setDestPostal('US' == $r->getDestCountry() ? substr($request->getDestPostcode(), 0, 5) : $request->getDestPostcode());
        }

        $r->setValue($request->getPackageValue());

        $r->setBaseSubtotalInclTax($request->getData('base_subtotal_incl_tax'));

        $r->setValueWithDiscount($request->getPackageValueWithDiscount());
        if (is_object($this->_getQuote()->getShippingAddress())) {
            $quoteShippingAddress = $this->_getQuote()->getShippingAddress();

            if($quoteShippingAddress->getLiftgateRequired()) {
                $r->setLiftgateRequired(true);
            } elseif (Mage::getStoreConfig('shipping/wsafreightcommon/hide_liftgate')){
                $defaultLiftgate = Mage::getStoreConfig('shipping/wsafreightcommon/default_liftgate',Mage::app()->getStore());
                $r->setLiftgateRequired($defaultLiftgate);
            } else {
                $r->setLiftgateRequired(false);
            }

            $type = $this->_getQuote()->getShippingAddress()->getShiptoType();

            if(is_null($type)) {
                $type = 0;
            }

            $r->setShiptoType($type);


            if($quoteShippingAddress->getInsideDelivery()) {
                $r->setInsideDelivery(true);
            } else {
                $r->setInsideDelivery(false);
            }

            if($quoteShippingAddress->getNotifyRequired()) {
                $r->setNotifyRequired(true);
            } else {
                $r->setNotifyRequired(false);
            }
        } else {
            $r->setLiftgateRequired(false);
            $r->setShiptoType(0);
            $r->setNotifyRequired(false);
            $r->setInsideDelivery(false);
        }

       if ($r->getNotifyRequired() || Mage::helper('wsafreightcommon')->isNotifyRequired()) {
           $fee = Mage::helper('wsafreightcommon')->getNotifyFee();
           if (is_numeric($fee) && $fee > 0) {
               $this->_topUpPrice += $fee;
           }
           $accArray[] = "NOTIFY";
       }

       if ($r->getLiftgateRequired() || Mage::helper('wsafreightcommon')->isFixedLiftgateFee()) {
           $fee = Mage::helper('wsafreightcommon')->getLiftgateFee($this->_code);
           if (is_numeric($fee) && $fee > 0) {
               $this->_topUpPrice += $fee;
           }
           $accArray[] = "LIFT";
       }
       $alwaysBusiness = Mage::getStoreConfig('shipping/wsafreightcommon/apply_live_business');

       if ($alwaysBusiness == '1'){
           $accArray[]="COM";
       } elseif (!is_null($r->getShiptoType() && !Mage::helper('wsafreightcommon')->isFixedDeliveryType())) {
           $shipToType = $r->getShiptoType();
           if ($shipToType=='0' || $shipToType=='Residential') {
               $fee=Mage::helper('wsafreightcommon')->getResidentialFee();
               if (is_numeric($fee) && $fee>0) {
                   $this->_topUpPrice += $fee;
               }
               $accArray[]="RES";
           } elseif ($shipToType=='2' || $shipToType=='Construction Site') {
               $accArray[]="CONSITE";
           } elseif ($shipToType=='3' || $shipToType=='Trade Show') {
               $accArray[]="TRADE";
           }

       }

        if ($r->getInsideDelivery()) { //todo add isFixedInsideFee
           $fee = Mage::helper('wsafreightcommon')->getInsideDeliveryFee();
           if (is_numeric($fee) && $fee > 0) {
               $this->_topUpPrice += $fee;
           }
           $accArray[] = "INSIDE";
        }
        $r->setOriginHazReqd(Mage::getStoreConfigFlag('shipping/wsafreightcommon/hazardous'));
   		if (Mage::getStoreConfigFlag('shipping/wsafreightcommon/hazardous')) {
       		$accArray[]="HAZ";

        }

        $r->setAccessories($accArray);
        $r->setIgnoreFreeItems(false);

        return $r;
    }

    protected function getResultSet($priceArr,$request,$response,$quoteId='') {

    	$path = 'carriers/'.$this->_code.'/';
    	$title = Mage::getStoreConfig($path.'title');
    	$defaultMethodTitle = Mage::helper('usa')->__(Mage::getStoreConfig($path.'name'));

      	$handlingProdFee =0;
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct')) {
        	$handlingProdFee = Mage::getModel('handlingproduct/handlingproduct')->getHandlingRateForItems($this->_request->getAllItems());
      		if ($this->_debug) {
        		Mage::helper('wsacommon/log')->postNotice($this->_code,'Handling Fee',$handlingProdFee);
      		}
        }

    	if ($this->_debug) {
        	Mage::helper('wsacommon/log')->postNotice($this->_code,'Price Arr',$priceArr);
      	}

		$result = Mage::getModel('shipping/rate_result');
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($title);
            $error->setErrorMessage(Mage::getStoreConfig($path.'specificerrmsg'));
            $result->append($error);

            Mage::helper('wsalogger/log')->postWarning($this->_code,'No rates found','');
            Mage::helper('wsalogger/log')->postWarning($this->_code,'====== REQUEST:===== ',$request);
           	Mage::helper('wsalogger/log')->postWarning($this->_code,'====== RESPONSE: ====== ',$response);
        } else {
            $min_shipping_cost = Mage::getStoreConfig($path.'min_cost');
        	$max_shipping_cost = Mage::getStoreConfig($path.'max_cost');
        	$apply_discount = Mage::getStoreConfig($path.'apply_discount');

            foreach ($priceArr as $method=>$price) {
                $originalMethod = '';
                $methodTitle = $this->_getTitleAndMethod($method,$defaultMethodTitle,count($priceArr),$originalMethod);
	            $rate = Mage::getModel('shipping/rate_result_method');
	            $rate->setCarrier($this->_code);
	            $rate->setCarrierTitle($title);
                if(empty($quoteId)) {
                    $rate->setMethodDescription('c-'.$originalMethod);
                } else {
                    $rate->setMethodDescription($quoteId);
                }
	            $rate->setMethod($method);

                if($price == 0 && Mage::getStoreConfig($path.'zero_fee_text') != '') {
                    $rate->setMethodTitle(Mage::helper('usa')->__(Mage::getStoreConfig($path.'zero_fee_text')));
                } else $rate->setMethodTitle($methodTitle);

                if (!empty($min_shipping_cost) && $min_shipping_cost>0) {
                    if (($price + $this->_topUpPrice) < $min_shipping_cost) {
                        $price = $min_shipping_cost;
                    }
                }

	            if (!empty($max_shipping_cost) && $max_shipping_cost > 0) {
					if (($price+$this->_topUpPrice) > $max_shipping_cost) {
						$price = $max_shipping_cost;
					}
				}

        		if (!empty($apply_discount) && is_numeric($apply_discount) && $apply_discount > 0) {
                    $discount_value = ($price/100) * $apply_discount;
					$price = $price - $discount_value;
				}

                if (Mage::getStoreConfig('carriers/freefreight/active') && $this->_code == 'freefreight') {
                    $rate->setPrice($price+$handlingProdFee);
                } else {
                    $rate->setPrice($price+$this->_topUpPrice+$handlingProdFee);
                }

	            $result->append($rate);
            }
        }
        return $result;
    }


    /**
     * Some freight carriers may want to return a generic carrier name e.g. LTL instead of YRC Freight in Cerasis Carrier
     * @param $method
     * @param $defaultMethodTitle
     * @param $numResults
     * @return mixed
     */
    protected function _getTitleAndMethod(&$method,$defaultMethodTitle,$numResults,&$originalMethodCode='') {
        $methodTitle = $method == $this->_code ? $defaultMethodTitle : $this->getCode('method', $method);
        if ($numResults==1 && Mage::getStoreConfig('shipping/wsafreightcommon/generic_name')!='') {
            $methodTitle = Mage::getStoreConfig('shipping/wsafreightcommon/generic_name');
            $originalMethodCode = $method;
            $method="GENERIC";
        }
        return $methodTitle;
    }


    protected function getShipmentMethods($allowedMethods) {
    	$shipmentMethods=array();
    	foreach ($allowedMethods as $method) {
    		$shipmentMethods[]=$this->getCode('method',$method);
    	}
    	return $shipmentMethods;
    }


    public function getAllowedMethods()
    {
        $arr = array();
    	$allowed = explode(',', $this->getConfigData('allowed_methods'));
     	foreach ($allowed as $k) {
             $arr[$k] =  $this->getCode('method', $k);
        }

        // Add generic Method
        if ( Mage::getStoreConfig('shipping/wsafreightcommon/generic_name')!='') {
            $arr['GENERIC'] = Mage::getStoreConfig('shipping/wsafreightcommon/generic_name');
        }



    	return $arr;
    }

}
