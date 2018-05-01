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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Model_Source_Ticket_Frontend_Selectors
{
    const DEPARTMENT_VALUE = 1;
    const PRIORITY_VALUE   = 2;
    const ORDER_VALUE      = 3;

    const DEPARTMENT_LABEL = 'Department Selector';
    const PRIORITY_LABEL   = 'Priority Selector';
    const ORDER_LABEL      = 'Order Selector';

    static public function toOptionArray($isMultiselect = false)
    {
        $options = array(
            array('value' => self::DEPARTMENT_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::DEPARTMENT_LABEL)),
            array('value' => self::PRIORITY_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::PRIORITY_LABEL)),
            array('value' => self::ORDER_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::ORDER_LABEL)),
        );
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('aw_hdu3')->__('--Please Select--')));
        }
        return $options;
    }
}