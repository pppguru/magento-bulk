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


class AW_Helpdesk3_Model_Source_Status
{
    const ENABLED_VALUE  = 1;
    const DISABLED_VALUE = 0;
    const DELETED_VALUE  = 2;

    const ENABLED_LABEL  = 'Enabled';
    const DISABLED_LABEL = 'Disabled';

    static public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ENABLED_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::ENABLED_LABEL)
            ),
            array(
                'value' => self::DISABLED_VALUE,
                'label' => Mage::helper('aw_hdu3')->__(self::DISABLED_LABEL)
            ),
        );
    }

    static public function toOptionHash()
    {
        return array(
            self::ENABLED_VALUE  => Mage::helper('aw_hdu3')->__(self::ENABLED_LABEL),
            self::DISABLED_VALUE => Mage::helper('aw_hdu3')->__(self::DISABLED_LABEL),
        );
    }
}