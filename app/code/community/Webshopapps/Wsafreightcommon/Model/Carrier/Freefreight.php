<?php
/* YRC Freight Shipping
 *
 * @category   Webshopapps
 * @package    Webshopapps_Freefreight
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Wsafreightcommon_Model_Carrier_Freefreight
    extends Webshopapps_Wsafreightcommon_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'freefreight';

    protected $_modName = 'Webshopapps_Wsafreightcommon';

    private $_freeFreightFees = 0;


    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $r = $this->setBaseRequest($request);

        $this->_rawRequest = $r;

        return $this;
    }

    protected function _getQuotes()
    {
        return $this->_getFeeIncRate();
    }

    protected function _getFeeIncRate()
    {
        $r = $this->_rawRequest;

        $resFee = Mage::helper('wsafreightcommon')->getFreeFreightResidentialFee();
        $liftFee = Mage::helper('wsafreightcommon')->getFreeFreightLiftgateFee();
        $insideFee = Mage::helper('wsafreightcommon')->getFreeFreightInsideDeliveryFee();

        $accArray=$r->getAccessories();
        foreach ($accArray as $acc) {
            switch ($acc) {
                case 'RES':
                    $this->_freeFreightFees += $resFee;
                    break;
                case 'LIFT':
                    $this->_freeFreightFees += $liftFee;
                    break;
                case 'INSIDE':
                    $this->_freeFreightFees += $insideFee;
                    break;
            }
        }

        if($this->_freeFreightFees != 0){
            //free freight plus fees
            $priceArr = array($this->_code => $this->_freeFreightFees);
        }
        else{
            //free freight
            $priceArr = array($this->_code => 0);
        }

        return $this->getResultSet($priceArr,$params=null,$response=null);

    }


    public function getCode($type, $code='')
    {
        $codes = array(
            'method'=>array(
                $this->_code    		=> Mage::helper('usa')->__('Freefreight'),
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


}
