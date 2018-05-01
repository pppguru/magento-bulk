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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Catalogpermissions_Model_Entity_Attribute_Source_Groups extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Returns period types as array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $customerGroups = Mage::getSingleton('adminhtml/system_config_source_customer_group')->toOptionArray();
        array_shift($customerGroups);

        array_unshift($customerGroups, array('value' => -1, 'label' => Mage::helper('salesrule')->__('NOT LOGGED IN')));
        array_unshift(
            $customerGroups, array('value' => 0, 'label' => Mage::helper('salesrule')->__('Disable functionality'))
        );
        return $customerGroups;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
