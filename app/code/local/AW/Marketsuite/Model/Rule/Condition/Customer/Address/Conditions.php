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


class AW_Marketsuite_Model_Rule_Condition_Customer_Address_Conditions extends Mage_Rule_Model_Condition_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('marketsuite/rule_condition_customer_address_conditions')->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(
            array(
                 'city'       => $hlp->__('City'),
                 'region_id'  => $hlp->__('State (Dropdown)'),
                 'region'     => $hlp->__('State (Text Field)'),
                 'country_id' => $hlp->__('Country'),
                 'telephone'  => $hlp->__('Telephone'),
                 'postcode'   => $hlp->__('Postcode'),
                 'company'    => $hlp->__('Company'),
            )
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            array(
                 '==' => Mage::helper('rule')->__('is'),
                 '!=' => Mage::helper('rule')->__('is not'),
            )
        );
        return $this;
    }

    public function getInputType()
    {
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'region_id':
            case 'country_id':
                return 'select';
            default:
                return 'text';
        }
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'region_id':
                    $options = Mage::helper('marketsuite')->getRegions();
                    break;

                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function validate(Varien_Object $object)
    {
        if ($this->getAttribute() == 'region_id') {
            return $this->validateAttribute($object->getData('region'));
        }
        return parent::validate($object);
    }

    public function getQuery($query)
    {
        return $query;
    }
}