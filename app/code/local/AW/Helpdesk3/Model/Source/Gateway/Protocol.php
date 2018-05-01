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


class AW_Helpdesk3_Model_Source_Gateway_Protocol
{
    const POP3_VALUE = 1;
    const IMAP_VALUE = 2;

    const POP3_LABEL = 'POP3';
    const IMAP_LABEL = 'IMAP';

    const POP3_INSTANCE = 'Zend_Mail_Storage_Pop3';
    const IMAP_INSTANCE = 'Zend_Mail_Storage_Imap';

    static public function toOptionArray()
    {
        return array(
            array('value' => self::POP3_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::POP3_LABEL)),
            array('value' => self::IMAP_VALUE, 'label' => Mage::helper('aw_hdu3')->__(self::IMAP_LABEL)),
        );
    }

    /**
     * @param int $protocol
     *
     * @return null | string
     */
    public function getInstanceByProtocol($protocol)
    {
        switch ($protocol) {
            case self::POP3_VALUE : $instance = self::POP3_INSTANCE;
                break;
            case self::IMAP_VALUE : $instance = self::IMAP_INSTANCE;
                break;
            default : $instance = null;
        }
        return $instance;
    }
}