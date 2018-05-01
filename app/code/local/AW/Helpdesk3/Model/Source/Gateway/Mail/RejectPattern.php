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


class AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern
{
    const HEADER_VALUE  = 1;
    const SUBJECT_VALUE = 2;
    const BODY_VALUE    = 3;

    const HEADER_LABEL  = 'Headers';
    const SUBJECT_LABEL = 'Subject';
    const BODY_LABEL    = 'Body';

    static public function toOptionArray()
    {
        return array(
            array('value' => self::HEADER_VALUE,  'label' => Mage::helper('aw_hdu3')->__(self::HEADER_LABEL)),
            array('value' => self::SUBJECT_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::SUBJECT_LABEL)),
            array('value' => self::BODY_VALUE,    'label' => Mage::helper('aw_hdu3')->__(self::BODY_LABEL))
        );
    }

    static public function toOptionHash()
    {
        return array(
            self::HEADER_VALUE  => Mage::helper('aw_hdu3')->__(self::HEADER_LABEL),
            self::SUBJECT_VALUE => Mage::helper('aw_hdu3')->__(self::SUBJECT_LABEL),
            self::BODY_VALUE    => Mage::helper('aw_hdu3')->__(self::BODY_LABEL),
        );
    }
}