<?php
/* UsaShipping
 *
 * User        karen
 * Date        1/19/14
 * Time        3:50 AM
 * @category   Webshopapps
 * @package    Webshopapps_Freightcommon
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Wsafreightcommon_Model_Carrier_Source_Displayrules {


    public function toOptionArray()
    {
        $freightCommon = Mage::getSingleton('wsafreightcommon/freightCommon');
        $arr = array();
        foreach ($freightCommon->getCode('display_rules') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('wsafreightcommon')->__($v));
        }
        return $arr;
    }

}