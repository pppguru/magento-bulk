<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

class Webshopapps_Wsaolddominion_Model_Carrier_Wsaolddominion_Source_Freightclass extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function toOptionArray()
    {
        $olddom = Mage::getSingleton('wsaolddominion/carrier_wsaolddominion');
        $arr = array();
        foreach ($olddom->getCode('freight_class') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }

        return $arr;
    }

    public function getAllOptions() {


        $arr = $this->toOptionArray();
        array_unshift($arr, array('value'=>'', 'label'=>Mage::helper('shipping')->__('Store Default')));
        return $arr;
    }
}
