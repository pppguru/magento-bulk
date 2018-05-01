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


require_once 'Mage/Customer/controllers/AccountController.php';
class AW_Mobile2_CustomerController extends Mage_Customer_AccountController
{
    protected function _getSession()
    {
        return Mage::getSingleton('aw_mobile2/customer_session');
    }

    public function getRefererUrl()
    {
        return $this->_getRefererUrl();
    }

    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn() && Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        }
        $session->setAfterAuthUrl($session->getBeforeAuthUrl());
        $this->_redirectUrl($this->getRefererUrl() . '#account');
    }
}