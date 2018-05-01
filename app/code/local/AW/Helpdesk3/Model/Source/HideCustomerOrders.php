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


class AW_Helpdesk3_Model_Source_HideCustomerOrders
{
    const FOR_ALL_AGENTS_VALUE       = 1;
    const EXCEPT_PRIMARY_AGENT_VALUE = 2;
    const EXCEPT_ALL_VALUE           = 3;

    const FOR_ALL_AGENTS_LABEL       = 'Hide from all Agents';
    const EXCEPT_PRIMARY_AGENT_LABEL = 'Hide from all Agents except Primary Agents';
    const EXCEPT_ALL_LABEL           = 'Don\'t hide';

    static public function toOptionArray()
    {
        return array(
            array(
                'value' => self::FOR_ALL_AGENTS_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::FOR_ALL_AGENTS_LABEL)
            ),
            array(
                'value' => self::EXCEPT_PRIMARY_AGENT_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::EXCEPT_PRIMARY_AGENT_LABEL)
            ),
            array(
                'value' => self::EXCEPT_ALL_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::EXCEPT_ALL_LABEL)
            ),
        );
    }
}