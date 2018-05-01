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


require_once 'Mage/Checkout/controllers/CartController.php';
class AW_Mobile2_CartController extends Mage_Checkout_CartController
{
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function addAction()
    {
        Mage::register('_no_redirect_flag', true, true);
        parent::addAction();
        $response = array(
            'top_cart_qty' => (int)Mage::getSingleton('checkout/cart')->getSummaryQty(),
            'message_box'  => '',
            'cart'         => ''
        );
        $layout = $this->getLayout();
        $messageBlock = $layout->createBlock('core/messages');
        $messageBlock->addMessages(Mage::getSingleton('checkout/session')->getMessages(true));
        $response['message_box'] = $messageBlock->getGroupedHtml();
        try {
            $update = $layout->getUpdate();
            $update->load('default');
            $layout->generateXml();
            $layout->generateBlocks();
            $cartBlock = $layout->getBlock('checkout.cart');
            if ($cartBlock) {
                $response['cart'] = $cartBlock->toHtml();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_ajaxResponse($response);
    }

    public function updateItemOptionsAction()
    {
        parent::updateItemOptionsAction();
        $this->getResponse()->clearHeader('Location');
        $this->_ajaxResponse(array(
            'redirect_to' => Mage::helper('checkout/url')->getCartUrl()
        ));
    }

    protected function _goBack()
    {
        if ($this->getRequest()->getActionName() == 'updatePost') {
            $backUrl = $this->_getRefererUrl();
            $this->getResponse()->setRedirect($backUrl . '#cart');
        }
        if ($this->getRequest()->getActionName() == 'couponPost') {
            $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER') . '#cart');
        }
        return $this;
    }

    protected function _redirectReferer($defaultUrl = null)
    {
        if ($this->getRequest()->getActionName() == 'delete') {
            $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER') . '#cart');
        } else if (!Mage::registry('_no_redirect_flag')) {
            parent::_redirectReferer($defaultUrl);
        }
        return $this;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('aw_mobile2/checkout_session');
    }
}