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
 * Class ET_IpSecurity_Model_Mysql4_Ipsecuritylog
 */
class ET_IpSecurity_Model_Mysql4_Ipsecuritylog extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Internal constructor
     */
    public function _construct()
    {
        // Note that the logid refers to the key field in your database table.
        $this->_init('etipsecurity/ipsecuritylog', 'logid');
    }
}