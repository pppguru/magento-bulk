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


/**
 * Class AW_Helpdesk3_Model_Gateway_Mail_RejectPattern
 * @method string getId()
 * @method string getTitle()
 * @method string getIsActive()
 * @method array getTypes()
 * @method string getPattern()
 */
class AW_Helpdesk3_Model_Gateway_Mail_RejectPattern extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/gateway_mail_rejectPattern');
    }

    /**
     * @param AW_Helpdesk3_Model_Gateway_Mail $mail
     *
     * @return bool
     */
    public function match($mail)
    {
        foreach ($this->getTypes() as $type) {
            if ($type == AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::HEADER_VALUE) {
                if (@preg_match($this->getPattern(), $mail->getHeaders())) {
                    return true;
                }
            }
            if ($type == AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::SUBJECT_VALUE) {
                if (@preg_match($this->getData('pattern'), $mail->getSubject())) {
                    return true;
                }
            }
            if ($type == AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::BODY_VALUE) {
                if (@preg_match($this->getData('pattern'), $mail->getBody())) {
                    return true;
                }
            }
        }
        return false;
    }
}