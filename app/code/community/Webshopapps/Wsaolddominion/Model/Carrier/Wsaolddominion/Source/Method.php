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

class Webshopapps_Wsaolddominion_Model_Carrier_Wsaolddominion_Source_Method
{
	public function toOptionArray()
	{
		$wsaolddominion = Mage::getSingleton('wsaolddominion/carrier_wsaolddominion');

		$arr = array();
		foreach ($wsaolddominion->getCode('method') as $k=>$v) {
			$arr[] = array('value'=>$k, 'label'=>$v);
		}
		return $arr;
	}
}