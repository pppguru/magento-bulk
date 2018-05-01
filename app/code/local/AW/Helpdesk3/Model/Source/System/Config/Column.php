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


class AW_Helpdesk3_Model_Source_System_Config_Column
{
    public function toOptionArray()
    {
        $helper = Mage::helper('aw_hdu3');
        $options = array(
            array('value' => 'id', 'label' => $helper->__('ID')),
            array('value' => 'last_message_date', 'label' => $helper->__('Last Message')),
            array('value' => 'department', 'label' => $helper->__('Department')),
            array('value' => 'agent', 'label' => $helper->__('Help Desk Agent')),
            array('value' => 'title', 'label' => $helper->__('Title')),
            array('value' => 'order_number', 'label' => $helper->__('Order #')),
            array('value' => 'customer', 'label' => $helper->__('Customer')),
            array('value' => 'priority', 'label' => $helper->__('Priority')),
            array('value' => 'store_view', 'label' => $helper->__('Store View')),
            array('value' => 'messages_count', 'label' => $helper->__('Messages')),
            array('value' => 'status', 'label' => $helper->__('Status')),
            array('value' => 'lock', 'label' => $helper->__('Lock')),
            array('value' => 'created_at', 'label' => $helper->__('Created')),
            array('value' => 'action', 'label' => $helper->__('Action'))
        );
        return $options;
    }
}