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
 * @package    AW_Mobile2
 * @version    2.0.6
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile2_Model_Source_Detection
{
    const AUTO_VALUE          = 1;
    const FORCE_MOBILE_VALUE  = 2;
    const FORCE_DESKTOP_VALUE = 3;

    const AUTO_LABEL          = 'Auto';
    const FORCE_MOBILE_LABEL  = 'Force Mobile View';
    const FORCE_DESKTOP_LABEL = 'Desktop View';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::AUTO_VALUE,
                'label' => Mage::helper('aw_mobile2')->__(self::AUTO_LABEL)
            ),
            array(
                'value' => self::FORCE_MOBILE_VALUE,
                'label' => Mage::helper('aw_mobile2')->__(self::FORCE_MOBILE_LABEL)
            ),
            array(
                'value' => self::FORCE_DESKTOP_VALUE,
                'label' => Mage::helper('aw_mobile2')->__(self::FORCE_DESKTOP_LABEL)
            )
        );
    }
}