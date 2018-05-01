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


class AW_Helpdesk3_Model_Source_Ticket_Lock
{
    const LOCKED_VALUE    = 1;
    const UNLOCKED_VALUE  = 0;

    const LOCKED_LABEL    = 'Locked';
    const UNLOCKED_LABEL  = 'Unlocked';

    static public function toOptionArray()
    {
        return array(
            array(
                'value' => self::LOCKED_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::LOCKED_LABEL)
            ),
            array(
                'value' => self::UNLOCKED_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::UNLOCKED_LABEL)
            ),
        );
    }

    static public function toOptionHash()
    {
        return array(
            self::LOCKED_VALUE  => Mage::helper('aw_hdu3')->__(self::LOCKED_LABEL),
            self::UNLOCKED_VALUE => Mage::helper('aw_hdu3')->__(self::UNLOCKED_LABEL),
        );
    }
}