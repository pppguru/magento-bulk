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
 * @copyright  Copyright (c) 2014 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Model_IpVariable
 */
class ET_IpSecurity_Model_IpVariable extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Option getter
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => 'REMOTE_ADDR',
                    'value' => 'REMOTE_ADDR'
                ),
                array(
                    'label' => 'HTTP_X_REAL_IP',
                    'value' => 'HTTP_X_REAL_IP'
                ),
                array(
                    'label' => 'HTTP_CLIENT_IP',
                    'value' => 'HTTP_CLIENT_IP'
                ),
                array(
                    'label' => 'HTTP_X_FORWARDED_FOR',
                    'value' => 'HTTP_X_FORWARDED_FOR'
                ),
                array(
                    'label' => 'HTTP_X_CLUSTER_CLIENT_IP',
                    'value' => 'HTTP_X_CLUSTER_CLIENT_IP'
                ),
            );
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }
}
