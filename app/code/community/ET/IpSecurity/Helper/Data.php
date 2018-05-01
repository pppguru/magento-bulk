<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Helper_Data
 */
class ET_IpSecurity_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns ip method which is selected in admin settings
     *
     * @return mixed
     */
    public function getIpVariable()
    {
        /** @var $model ET_IpSecurity_Model_IpVariable */
        $model = Mage::getModel('etipsecurity/ipVariable');
        $ipsArray = $model->getOptionArray();

        $configVariable = Mage::getStoreConfig('etipsecurity/global_settings/get_ip_method');

        if (!in_array($configVariable, $ipsArray)) {
            $configVariable = 'REMOTE_ADDR';
        }

        return $configVariable;
    }
}