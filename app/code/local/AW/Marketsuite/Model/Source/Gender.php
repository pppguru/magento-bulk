<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Model_Source_Gender
{
    const NOT_SPECIFIED = -1;
    const NOT_SPECIFIED_LABEL = 'Not Specified';

    protected $_optionArray = null;

    public function __construct()
    {
        if (is_null($this->_optionArray)) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
            $options = $attribute->getSource()->getAllOptions();

            foreach ($options as &$option) {
                if ($option['label'] == '' && $option['value'] == '') {
                    $option['label'] = Mage::helper('marketsuite')->__(self::NOT_SPECIFIED_LABEL);
                    $option['value'] = self::NOT_SPECIFIED;
                }
                $option['label'] = Mage::helper('marketsuite')->__($option['label']);
            }
            $this->_optionArray = $options;
        }
    }

    public function toOptionArray()
    {
        return $this->_optionArray;
    }

    /**
     * Returns associative array $value => $label
     *
     * @return array
     */
    public function toOptionHash()
    {
        $_options = array();
        foreach ($this->toOptionArray() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}

