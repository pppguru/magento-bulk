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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


require_once 'Mage/Checkout/controllers/CartController.php';
class AW_Mobile3_CartController extends Mage_Checkout_CartController
{
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    protected function _getCartBlockForResponse($coupon = false){
        $response = array(
            'count'    => (int)Mage::getSingleton('checkout/cart')->getSummaryQty(),
            'message'  => '',
            'cart'     => ''
        );
        $layout = $this->getLayout();
        if(!$coupon){
            $messageBlock = $layout->createBlock('core/messages');
            $messageBlock->addMessages(Mage::getSingleton('checkout/session')->getMessages(true));
            $response['message'] = $messageBlock->getGroupedHtml();
        }
        try {
            $update = $layout->getUpdate();
            $update->load('default');
            $layout->generateXml();
            $layout->generateBlocks();
            $cartBlock = $layout->getBlock('checkout.cart');
            if ($cartBlock) {
                $response['cart'] = $cartBlock->toHtml();
            }
            if($coupon){
                $couponBlock = $layout->getBlock('checkout.cart.coupon');
                if ($couponBlock) {
                    $response['discount'] = $couponBlock->toHtml();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $response;
    }

    public function addAction()
    {
        $params = $this->getRequest()->getParams();
        if(!isset($params['file_field_exist'])){
            Mage::register('_no_redirect_flag', true, true);
            parent::addAction();
            $response =  $this->_getCartBlockForResponse();

            $this->_ajaxResponse($response);
        }else{
            Mage::register('_redirect_flag', true, true);
            parent::addAction();
        }
    }

    public function updateItemOptionsAction()
    {
        parent::updateItemOptionsAction();

        $params = $this->getRequest()->getParams();
        if(!isset($params['file_field_exist'])){
            $this->getResponse()->clearHeader('Location');
            $this->_ajaxResponse(array(
                'redirect_to' => Mage::helper('checkout/url')->getCartUrl()
            ));
        }else{
            $this->getResponse()->setRedirect(Mage::helper('checkout/url')->getCartUrl());
        }
    }

    protected function _goBack()
    {
        if (Mage::registry('_redirect_flag')) {
            $refererUrl = (Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl()).'#cart';
            $this->getResponse()->setRedirect($refererUrl);
        }
        if ($this->getRequest()->getActionName() == 'updatePost') {
            $response =  $this->_getCartBlockForResponse();
            $this->_ajaxResponse($response);
        }
        if ($this->getRequest()->getActionName() == 'couponPost') {
            $response =  $this->_getCartBlockForResponse(true);
            $this->_ajaxResponse($response);
        }
        return $this;
    }

    protected function _redirectReferer($defaultUrl = null)
    {
        if ($this->getRequest()->getActionName() == 'delete') {
            $response =  $this->_getCartBlockForResponse();
            $this->_ajaxResponse($response);
        } else if (!Mage::registry('_no_redirect_flag')) {
            parent::_redirectReferer($defaultUrl);
        }
        return $this;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('aw_mobile3/checkout_session');
    }
}