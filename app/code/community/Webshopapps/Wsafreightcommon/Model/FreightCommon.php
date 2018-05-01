<?php
/* UsaShipping
 *
 * User        karen
 * Date        1/19/14
 * Time        4:45 AM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Wsafreightcommon_Model_FreightCommon extends Mage_Core_Model_Abstract {


    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code='')
    {

        $codes = array(
            'display_rules' => array(
                'product_ships_freight'     => Mage::helper('wsafreightcommon')->__('Freight class set on product'),
                'weight'                    => Mage::helper('wsafreightcommon')->__('Weight Threshold reached'),
                'dimensions'                => Mage::helper('wsafreightcommon')->__('Dimension Threshold reached'),
                'weight_dims'               => Mage::helper('wsafreightcommon')->__('Both Weight & Dimension Threshold reached'),
                'min_dims_length'           => Mage::helper('wsafreightcommon')->__('Minimum Package Length'),

            ),
            'ship_rules' => array(
                'product_freight'           => Mage::helper('wsafreightcommon')->__('Freight class set on product'),
                'product_freight_and_must'  => Mage::helper('wsafreightcommon')->__('Freight class set on product & Must Ship Freight set'),
                'product_must'              => Mage::helper('wsafreightcommon')->__('Must Ship Freight Set'),
                'weight'                    => Mage::helper('wsafreightcommon')->__('Weight Threshold reached'),
                'dimensions'                => Mage::helper('wsafreightcommon')->__('Dimension Threshold reached'),
                'weight_dims'               => Mage::helper('wsafreightcommon')->__('Both Weight & Dimension Threshold reached'),
                'min_dims_length'           => Mage::helper('wsafreightcommon')->__('Minimum Package Length'),

            ),
            'dest_type' => array(
                '0' => 'RES',
                '1' => 'COM',
                '2' => 'COM',//Covers for building site, trade show etc
                '3' => 'COM',
                '4' => 'COM',
                '5' => 'COM',
            ),
            'dest_type_reverse' => array(
                '0' => 'COM',
                '1' => 'RES',
                '2' => 'COM',//Covers for building site, trade show etc
                '3' => 'COM',
                '4' => 'COM',
                '5' => 'COM',
            )
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
}