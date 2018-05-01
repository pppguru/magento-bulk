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


class AW_Mobile2_Model_Observer
{
    const TRIGGER_REQUEST_PARAM = 'aw_mobile2';

    public function beforeRenderLayout()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (!$request->getParam(self::TRIGGER_REQUEST_PARAM, false)) {
            return $this;
        }
        unset($_GET[self::TRIGGER_REQUEST_PARAM]);
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = str_replace(self::TRIGGER_REQUEST_PARAM . '=1', '', $requestUri);
        $_SERVER['REQUEST_URI'] = substr_replace($requestUri, '', -1);

        $layout = Mage::app()->getFrontController()->getAction()->getLayout();
        $layout->setDirectOutput(false);
        $layout->getOutput();
        try {
            $result = $this->_getHtml($layout);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_sendResponse(
            Zend_Json::encode($result)
        );
        return $this;
    }

    public function loadBeforeLayout()
    {
        $this->_checkDeviceOS();
        $this->_checkRedirects();
        $this->_checkHomePage();
        return $this;
    }

    protected function _checkHomePage()
    {
        if ((!Mage::helper('aw_mobile2')->isIphoneTheme() && !Mage::helper('aw_mobile2')->isIPadTheme())
        ) {
            return $this;
        }
        $cmsActions = array(
            'cms/index/index',
            'cms/index/defaultIndex'
        );
        $controllerAction = Mage::app()->getFrontController()->getAction();
        $fullActionName = $controllerAction->getFullActionName('/');
        if (!in_array($fullActionName, $cmsActions)) {
            return $this;
        }
        if ($fullActionName == 'cms/index/defaultIndex' && !Mage::registry('is_default_index_action_flag')) {
            $controllerAction->getLayout()->getUpdate()->removeHandle('cms_index_defaultindex');
        }
        if (Mage::registry('is_home_page_flag')) {
            return $this;
        }

        $request = Mage::app()->getFrontController()->getRequest();
        $response = Mage::app()->getFrontController()->getResponse();
        $requestedPageId = $request->getParam('page_id', $request->getParam('id', false));
        $cmsPage = Mage::getModel('cms/page')->load($requestedPageId);
        $mobileHomePageId = Mage::helper('aw_mobile2/config')->getIPhoneHomePageId();
        if (Mage::helper('aw_mobile2')->isIPadTheme()) {
            $mobileHomePageId = Mage::helper('aw_mobile2/config')->getIPadHomePageId();
        }
        if ($cmsPage->getIdentifier() == $mobileHomePageId) {
            $response->setRedirect(Mage::getBaseUrl(), 301)->sendHeaders();
            exit;
        }

        Mage::register('is_home_page_flag', true);
        Mage::getSingleton('cms/page')->unsetData();
        if (!Mage::helper('cms/page')->renderPage($controllerAction, $mobileHomePageId)) {
            Mage::register('is_default_index_action_flag', true);
            $controllerAction->getLayout()->getUpdate()->addHandle('cms_index_defaultindex');
            $controllerAction->defaultIndexAction();
        }
        $response->sendResponse();
        exit;
        return $this;
    }

    protected function _checkRedirects()
    {
        if (!Mage::helper('aw_mobile2')->isIphoneTheme()) {
            return $this;
        }
        $customerActions = array(
            'customer/account/login',
            'customer/account/logoutsuccess',
            'customer/account/create',
            'customer/account/forgotpassword',
            'aw_mobile2/customer/create',
            'aw_mobile2/customer/index',
            'aw_mobile2/customer/login',
            'aw_mobile2/customer/logoutsuccess',
            'aw_mobile2/customer/forgotpassword',
        );
        if (in_array(Mage::app()->getFrontController()->getAction()->getFullActionName('/'), $customerActions)) {
            $this->_sendRedirectResponse(Mage::getUrl('#account'));
        }
        $checkoutActions = array(
            'checkout/cart/index',
            'checkout/multishipping/index',
        );
        if (in_array(Mage::app()->getFrontController()->getAction()->getFullActionName('/'), $checkoutActions)) {
            $this->_sendRedirectResponse(Mage::getUrl('#cart'));
        }
        return $this;
    }

    protected function _checkDeviceOS()
    {
        if ((!Mage::helper('aw_mobile2')->isIphoneTheme() && !Mage::helper('aw_mobile2')->isIPadTheme())
        ) {
            return $this;
        }
        $typeOS = null;
        if (Mage::helper('aw_mobile2')->isAndroidDevice()) {
            $typeOS = 'android';
        }
        Mage::app()->getFrontController()
            ->getAction()
            ->getLayout()
            ->getUpdate()
            ->addHandle('device_OS_type_' . (is_null($typeOS) ? 'default' : $typeOS))
        ;
    }

    protected function _getHtml($layout)
    {
        $handles = $layout->getUpdate()->getHandles();
        $result = array('content' => '');
        if (in_array('catalog_category_default', $handles)
            || in_array('catalog_category_layered', $handles)
            || in_array('catalogsearch_result_index', $handles)
            || in_array('awadvancedsearch_result_index', $handles)
        ) {
            $result['content'] = $this->_getCategoryHtml($layout);
            $result['layer'] = $this->_getLayerHtml($layout);
            $result['pager'] = $this->_getPagerHtml($layout, 'product_list_toolbar_pager');
        } else {
            $result['content'] = $this->_getPagerHtml($layout, 'mobile_list_toolbar_pager');
        }
        return $result;
    }

    protected function _getCategoryHtml($layout)
    {
        $content = '';
        //init layer filters
        $catalogTopBlock = $layout->getBlock('category.top');
        if ($catalogTopBlock) {
            $content = $catalogTopBlock->toHtml();
        }

        //init product list
        $productListBlock = $layout->getBlock('product_list');
        if (!$productListBlock) {
            $productListBlock = $layout->getBlock('search_result_list');
        }
        if (!$productListBlock) {
            $productListBlock = $layout->getBlock('search_result_catalog');
        }
        if ($productListBlock) {
            $content = $productListBlock->toHtml() . $content;
        }
        return $content;
    }

    protected function _getPagerHtml($layout, $name)
    {
        $content = "";
        $pagerBlock = $layout->getBlock($name);
        if ($pagerBlock) {
            $content = $pagerBlock->toHtml();
        }
        return $content;
    }

    protected function _getLayerHtml($layout)
    {
        $content = "";
        $layerBlock = $layout->getBlock('catalog.layer');
        if (!$layerBlock) {
            $layerBlock = $layout->getBlock('search.layer');
        }
        if (!$layerBlock) {
            $layerBlock = $layout->getBlock('advancedsearch.leftnav');
        }
        if ($layerBlock) {
            $content = $layerBlock->toHtml();
        }
        return $content;
    }

    private function _sendResponse($html)
    {
        $response = Mage::app()->getResponse();
        $response->clearBody();
        $response->setHttpResponseCode(200);
        //remove location header from response
        $headers = $response->getHeaders();
        $response->clearHeaders();
        foreach ($headers as $header) {
            if ($header['name'] !== 'Location') {
                $response->setHeader($header['name'], $header['value'], $header['replace']);
            }
        }
        $response->sendHeaders();
        echo $html;
        exit(0);
    }

    protected function _sendRedirectResponse($url)
    {
        if (Mage::getModel('core/store')->isCurrentlySecure() && $this->_isAjax()) {
            $response = new Varien_Object();
            $response->setData('redirectUrl', $url);
            echo $response->toJson();
        } else {
            $response = Mage::app()->getFrontController()->getResponse();
            $response->setRedirect($url);
            $response->sendResponse();
        }
        exit;
    }

    public function checkForgotpassword($observer)
    {
        if (@class_exists('Mage_Captcha_Model_Observer')) {
            return Mage::getModel('captcha/observer')->checkForgotpassword($observer);
        }
        return $this;
    }

    public function checkUserLogin($observer)
    {
        if (@class_exists('Mage_Captcha_Model_Observer')) {
            $formId = 'user_login';
            $captchaParams = Mage::app()->getRequest()->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
            if(!isset($captchaParams[$formId])) {
                $formId = 'user_checkout_login';
            }
            $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
            $controller = $observer->getControllerAction();
            $loginParams = $controller->getRequest()->getPost('login');
            $login = isset($loginParams['username']) ? $loginParams['username'] : null;
            if ($captchaModel->isRequired($login)) {
                $word = $this->_getCaptchaString($controller->getRequest(), $formId);
                if (!$captchaModel->isCorrect($word)) {
                    Mage::getSingleton('customer/session')->addError(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    Mage::getSingleton('customer/session')->setUsername($login);
                    $beforeUrl = Mage::getSingleton('customer/session')->getBeforeAuthUrl();
                    $url =  $beforeUrl ? $beforeUrl : Mage::helper('customer')->getLoginUrl();
                    $controller->getResponse()->setRedirect($url);
                }
            }
            $captchaModel->logAttempt($login);
            return $this;
        }
        return $this;
    }

    protected function _getCaptchaString($request, $formId)
    {
        $captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
        return $captchaParams[$formId];
    }

    public function checkUserCreate($observer)
    {
        if (@class_exists('Mage_Captcha_Model_Observer')) {
            return Mage::getModel('captcha/observer')->checkUserCreate($observer);
        }
        return $this;
    }

    public function sendResponseBefore($observer)
    {
        if ((!Mage::helper('aw_mobile2')->isIphoneTheme() && !Mage::helper('aw_mobile2')->isIPadTheme())
        ) {
            return $this;
        }
        $secureControllers = array(
            'checkout/onepage',
            'checkout/multishipping',
            'customer/account',
            'aw_mobile2/customer'
        );
        $request = Mage::app()->getFrontController()->getRequest();
        if (!in_array($request->getRequestedRouteName() . '/' . $request->getRequestedControllerName(), $secureControllers)) {
            return $this;
        }
        if ($this->_isAjax()) {
            $response = $observer->getResponse();
            foreach ($response->getHeaders() as $header) {
                if (strtolower('Location') == strtolower($header['name'])) {
                    $url = $header['value'];
                    if (Mage::getModel('core/store')->isCurrentlySecure()) {
                        $uri = Zend_Uri::factory($url);
                        if ($uri instanceof Zend_Uri_Http) {
                            $url = explode(':', $url, 2);
                            $response->setHeader('Location', 'https:' . $url[1], true);
                        }
                        return $this;
                    }
                }
            }
        }
        return $this;
    }

    protected function _isAjax()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        return ($request->isXmlHttpRequest() || $request->getParam('ajax') || $request->getParam('isAjax'));
    }
	
	/**
	**This observer method is called prior to saving shipping method. We have added the observer event in the config.xml. Mohin, 26 Oct 2015
	*/
	public function removeNonShippableProducts(Varien_Event_Observer $observer)
    {
		Mage::helper('restrictcountry')->removeNonShippableProducts();       
    }
}