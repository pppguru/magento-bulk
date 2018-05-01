<?php

/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Wsaolddominion
 * User         Genevieve Eddison
 * Date         19 May 2013
 * Time         09:00
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */



class Webshopapps_Wsaolddominion_Model_Carrier_Wsaolddominion
extends Webshopapps_Wsafreightcommon_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{

	protected $_code = 'wsaolddominion';

	protected $_modName = 'Webshopapps_Wsaolddominion';

    protected $_prodRateQuoteServiceWsdl = 'https://www.odfl.com/wsRate_v4/RateService/WEB-INF/wsdl/RateService.wsdl';

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
	{
		$r = $this->setBaseRequest($request);

        $this->_setAccessRequest($r);

		$this->_rawRequest = $r;

		return $this;
	}

    protected function _setAccessRequest(&$r){
        $r->setCustomerAccount($this->getConfigData('cust_account'));
        $r->setUsername($this->getConfigData('user_name'));
        $r->setPassword($this->getConfigData('password'));
    }

	protected function _formRateRequest()
	{
		$r = $this->_rawRequest;

        $ratesRequest = new stdClass();
        $ratesRequest->arg0 = new stdClass();
        $ratesRequest->arg0->odfl4MeUser = $r->getUsername();
        $ratesRequest->arg0->odfl4MePassword = $r->getPassword();
        $ratesRequest->arg0->odflCustomerAccount = $r->getCustomerAccount();
        $ratesRequest->arg0->originCountry = $r->getOrigCountryIso3();
        $ratesRequest->arg0->originPostalCode = preg_replace('/\s+/', '', $r->getOrigPostal());
        $ratesRequest->arg0->destinationPostalCode = preg_replace('/\s+/', '', $r->getDestPostal());
        $ratesRequest->arg0->destinationCountry = $r->getDestCountryIso3();
        $freightarray = array();
        foreach($this->getLineItems($r->getIgnoreFreeItems()) as $class => $weight) {
                $freightarray[] = array('ratedClass'=>$class,'weight'=>$weight);

        }
        $ratesRequest->arg0->freightItems=$freightarray;
        $ratesRequest->arg0->requestReferenceNumber=false;

        $accessories = $this->_getAccessories($r);
        if (count($accessories) > 0) {
            $ratesRequest->arg0->accessorials = $accessories;
        }

		if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3Nhb2xkZG9taW5pb24vc2hpcF9vbmNl',
				'Ymx1ZWxpemFyZA==','Y2FycmllcnMvd3Nhb2xkZG9taW5pb24vc2VyaWFs')) {
		    return null;
		}


		if ($this->_debug) {
			Mage::helper('wsalogger/log')->postInfo('wsaolddominion','Request',$ratesRequest);
		}

        return $ratesRequest;
	}

    protected function _getQuotes()
    {
        $ratesRequest = $this->_formRateRequest();
        $requestString = serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $ratesRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->getLTLRateEstimate($ratesRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }
        if($this->_debug)
        {
            Mage::helper('wsalogger/log')->postInfo('wsaolddominion','Response',$debugData);
        }
        return $this->_parseRateResponse($ratesRequest,$response);
    }

	protected function _parseRateResponse($ratesRequest,$response)
	{

		$costArr = array();
		$priceArr = array();
		$quoteId=''; // quote id not available

		if (is_object($response)) {

			$resp = $response->return;
            if (!$resp->success) {
                $errorTitle = 'No result returned.';
                if ($resp->errorMessages) {
                    if (is_array($resp->errorMessages))
                    {
                        foreach ($resp->errorMessages as $error) {
                            $errorTitle.= ' ' .(string)$error;
                        }
                    }
                    else {
                        $errorTitle = (string)$resp->errorMessages;
                    }
                }
            }
            else if (isset($resp) && $resp->success) {
                $carrier = $resp->rateEstimate;
                $amount = (string)$carrier->netFreightCharge;
                $costArr[$this->_code]  = $amount;
                $priceArr[$this->_code] = $this->getMethodPrice($amount);

			}
		}

		return $this->getResultSet($priceArr,$ratesRequest,$response,'');

	}

	public function getCode($type, $code='')
	{
		$codes = array(
                'method'=>array(
                    $this->_code            => Mage::helper('usa')->__('TTL'),
                ),
                'freight_class' => array(
                    '50' 	=> '50',
                    '55' 	=> '55',
                    '60' 	=> '60',
                    '65' 	=> '65',
                    '70' 	=> '70',
                    '77.5' 	=> '77.5',
                    '85' 	=> '85',
                    '92.5' 	=> '92.5',
                    '100' 	=> '100',
                    '110' 	=> '110',
                    '125' 	=> '125',
                    '150' 	=> '150',
                    '175' 	=> '175',
                    '200' 	=> '200',
                    '250' 	=> '250',
                    '300' 	=> '300',
                    '400' 	=> '400',
                    '500' 	=> '500',
                ),
		);

		if (!isset($codes[$type])) {
			return false;
		} elseif (''===$code) {
			return $codes[$type];
		}

		if (!isset($codes[$type][$code])) {
			return false;
		} else {
			return $codes[$type][$code];
		}
	}

    /**
     * Get selected Accessorials and add to request
     */
    protected function _getAccessories($r) {


        if (!Mage::helper('wsafreightcommon')->getUseLiveAccessories()) {
            return null;
        }
        $accessorials = array();

        $accSettings=$r->getAccessories();

        foreach ($accSettings as $acc) { // Add accessorials to the XML Request
            switch ($acc) {
                case 'RES':
                    $accessorials[] = 'RDC';
                    break;
                case 'LIFT_ORIGIN':
                    $accessorials[] = 'HYO';
                    break;
                case 'LIFT':
                    $accessorials[] = 'HYD';
                    break;
                case 'INSIDE':
                    $accessorials[] = 'IDC';
                    break;
                case 'NOTIFY':
                    $accessorials[] = 'ARN';
                    break;
                case 'HAZ':
                    $accessorials[] = 'HAZ';
                    break;
            }
        }

        if (empty($accessorials)) {
            return null;
        }

        return $accessorials;
    }

    /**
     * Create rate soap client
     *
     * @return SoapClient
     */
    protected function _createRateSoapClient()
    {
            return $this->_createSoapClient($this->_prodRateQuoteServiceWsdl,$this->_debug);
    }

    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = new SoapClient($wsdl, array('trace' => $trace));

        return $client;
    }


    protected function getLineItems($ignoreFreeItems, $useParent=true, $getDimensional = false) {

        // override use of $useParent and get from freight common instead
        $useParent = Mage::getStoreConfig('shipping/wsafreightcommon/use_parent');

        $LineItemArray=array();
        $defaultFreightClass = Mage::helper('wsafreightcommon')->getDefaultFreightClass();
        $defaultClassText = Mage::helper('shipping')->__('Store Default');

        foreach ($this->_request->getAllItems() as $item) {

            $weight=0;
            $qty=0;
            $price=0;

            if (!Mage::helper('wsacommon/shipping')->getItemTotals($item, $weight,$qty,$price,$useParent,$ignoreFreeItems)) {
                continue;
            }

            $product = Mage::helper('wsacommon/shipping')->getProduct($item,$useParent);

            $weight=ceil($weight);  // round up to nearest whole - required for conway
            $class=$product->getAttributeText('freight_class_select');

            if (empty($class) || $class=='' || $class==$defaultClassText) {
                $class=$defaultFreightClass; // use default
            }

            if (empty($LineItemArray) || !array_key_exists($class,$LineItemArray)) {
                $LineItemArray[$class]= $weight;
            } else {
                $LineItemArray[$class]= $LineItemArray[$class]+ ($weight);
            }
        }
        return $LineItemArray;
    }
}