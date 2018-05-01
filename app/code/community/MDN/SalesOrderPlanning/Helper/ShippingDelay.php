<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_SalesOrderPlanning_Helper_ShippingDelay extends Mage_Core_Helper_Abstract
{
	
	/**
	 * Init or Update the carriers list from the existing Orders + current Conf
	 *
	 */
	public function updateCarriers()
	{
		$collection = $this->getShippingListAsArray();
		$shippingDelay = Mage::getStoreConfig('planning/delivery/default_shipping_delay');

		foreach ($collection as $code => $title)
		{
			if (!$this->carrierIsPresent($code))
			{
				mage::getModel('SalesOrderPlanning/ShippingDelay')
						->setpsd_carrier($code)
						->setpsd_carrier_title($title)
						->setpsd_default($shippingDelay)
						->save();
			}
		}

	}
	
	/**
	 * Check if a carrier is present in table
	 *
	 * @param unknown_type $code
	 */
	public function carrierIsPresent($code)
	{
		$collection = mage::getModel('SalesOrderPlanning/ShippingDelay')->getCollection()->addFieldToFilter('psd_carrier', $code);
		
		return ($collection->getSize() > 0);
	}

	/**
	 * @return null
	 */
	public function getShippingListAsArray(){
		if ($this->_ordersShippingMethods == null) {

			$shippingMethodsFromOrderSql = $this->getShippingMethodFromOrderSql();

			$shippingMethodsFromConf = $this->getShippingMethodFromConf();

			$this->_ordersShippingMethods = array_merge($shippingMethodsFromOrderSql,$shippingMethodsFromConf);
		}
		return $this->_ordersShippingMethods;
	}
	
	/**
	 * Return the correct shipping delay for carrier
	 *
	 */
	public function getShippingDelayForCarrier($shippingMethod, $Country)
	{
		$return = Mage::getStoreConfig('planning/delivery/default_shipping_delay');

		//define carrier
		$carrier = '';
		$t = explode('_', $shippingMethod);
		if (count($t) > 0)
			$carrier = $shippingMethod;

		//load shipping delay for carrier
		$item = mage::getModel('SalesOrderPlanning/ShippingDelay')->load($carrier, 'psd_carrier');
		if ($item->getId())
		{
			$return = $item->getpsd_default();

			//check in exceptions
			if ($item->getpsd_exceptions() != '')
			{
				$exceptions = explode(',', $item->getpsd_exceptions());
				for($i=0; $i<count($exceptions); $i++)
				{
					$values = explode(':', $exceptions[$i]);
					if (count($values) == 2)
					{
						if ($Country == $values[0])
							$return = $values[1];
					}
				}
			}
		}
		
		return $return;
	}

    private $_readConnection = null;
    private $_ordersShippingMethods = null;




    private function getReadConnection(){
        if(!$this->_readConnection){
            $this->_readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        return $this->_readConnection;
    }



    private function getShippingMethodFromOrderSql() {
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'SELECT distinct shipping_method, shipping_description FROM '.$prefix.'sales_flat_order';
        $methods = $this->getReadConnection()->fetchAll($sql);

        $methodArrays = array();
        foreach($methods as $method)
        {
            list($carrierCode,$methodCode) = explode('_',$method['shipping_method']);
            $key = $carrierCode.'_'.$methodCode;
			if(strlen($key)>1)
            	$methodArrays[$key] = '['.ucfirst($carrierCode).'] '.$method['shipping_description'];
        }
        return $methodArrays;
    }

    public function getShippingMethodFromConf()
    {
        $methods = array();
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (!$carrierModel->isActive()) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');

            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $key = $carrierCode.'_'.$methodCode;
				if(strlen($key)>1)
                	$methods[$key] = '['.$carrierTitle.'] '.$methodTitle;
            }
        }

        return $methods;
    }
	
}