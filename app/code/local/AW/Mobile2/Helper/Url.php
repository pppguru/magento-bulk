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


class AW_Mobile2_Helper_Url extends Mage_Core_Helper_Url
{
    public function getCartUrl()
    {
        return $this->_getUrl('aw_mobile2/cart/content') . $this->_getNoCacheParam();
    }

    public function getAddToCartUrl(
        $product, $additional = array(), $submitRouteData = null
    )
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

        $routeParams = array(
            $urlParamName   => $continueUrl,
            'product'       => $product->getEntityId()
        );

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_store_to_url'] = true;
        }
        if ($submitRouteData) {
            $route = str_replace('checkout/', 'aw_mobile2/', $submitRouteData['route']);
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : array();
            return $this->_getUrl($route, $params) . $this->_getNoCacheParam();
        }
        return $this->_getUrl('aw_mobile2/cart/add', $routeParams) . $this->_getNoCacheParam();
    }

    protected function _getNoCacheParam()
    {
        if (Mage::app()->useCache('full_page')) {
            return '?no_cache=true';
        }
        return '';
    }

    public function getCartUpdateUrl()
    {
        return $this->_getUrl('aw_mobile2/cart/updatePost') . $this->_getNoCacheParam();
    }

    public function getCartDeleteUrl($itemId)
    {
        return $this->_getUrl(
            'aw_mobile2/cart/delete',
            array(
                'id' => $itemId,
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl()
            )
        ) . $this->_getNoCacheParam();
    }

    public function getCartCouponPostUrl()
    {
        return $this->_getUrl('aw_mobile2/cart/couponPost') . $this->_getNoCacheParam();
    }

    public function getCustomerAccountUrl()
    {
        return $this->_getUrl('aw_mobile2/customer/index') . $this->_getNoCacheParam();
    }

    public function getLoginUrl()
    {
        return $this->_getUrl('aw_mobile2/customer/login') . $this->_getNoCacheParam();
    }

    public function getCustomerPostActionUrl()
    {
        return $this->_getUrl('aw_mobile2/customer/loginPost') . $this->_getNoCacheParam();
    }

    public function getRegisterPostUrl()
    {
        return $this->_getUrl('aw_mobile2/customer/createpost') . $this->_getNoCacheParam();
    }

    public function getForgotPostUrl()
    {
        return $this->_getUrl('aw_mobile2/customer/forgotpasswordpost') . $this->_getNoCacheParam();
    }

    public function getProductUrl($product)
    {
        return $product->getUrlModel()->getUrl($product);
    }

    public function getExternalUrl($path)
    {
        return Zend_Uri::factory($this->_getUrl('*/*/*'))->getScheme() . "://" . $path;
    }

    protected function _getUrl($route, $params = array())
    {
        if (Mage::getModel('core/store')->isCurrentlySecure()) {
            $params = array_merge($params, array('_secure' => true));
        }
        return parent::_getUrl($route, $params);
    }
}