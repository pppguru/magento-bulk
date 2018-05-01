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


class AW_Helpdesk3_Model_Source_Gateway_Secure
{
    const TYPE_NONE_VALUE = 0;
    const TYPE_SSL_VALUE  = 1;
    const TYPE_TLS_VALUE  = 2;

    const TYPE_NONE_LABEL = 'None';
    const TYPE_SSL_LABEL  = 'SSL';
    const TYPE_TLS_LABEL  = 'TLS';

    const TYPE_SSL_CODE  = 'SSL';
    const TYPE_TLS_CODE  = 'TLS';

    static public function toOptionArray()
    {
        return array(
            array('value' => self::TYPE_NONE_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::TYPE_NONE_LABEL)),
            array('value' => self::TYPE_SSL_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::TYPE_SSL_LABEL)),
            array('value' => self::TYPE_TLS_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::TYPE_TLS_LABEL)),
        );
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public function getTypeCodeByValue($value)
    {
        switch ($value) {
            case self::TYPE_TLS_VALUE : $code = self::TYPE_TLS_CODE;
                break;
            case self::TYPE_SSL_VALUE : $code = self::TYPE_SSL_CODE;
                break;
            default : $code = false;
        }
        return $code;
    }
}