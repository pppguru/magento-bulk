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


class AW_Mobile3_Model_Observer
{
    const TRIGGER_REQUEST_PARAM = 'aw_mobile3';
    const SWITCH_TO_PARAM = 'aw_mobile3_switch';

    public function pageLoadBeforeFront()
    {
        if(Mage::helper('aw_mobile3')->isCanShowMobileVersion()) {
            $config = Mage::getConfig();
            //remove wishlist layout on magento < 1.7.0
            if(version_compare(Mage::getVersion(), '1.7.0', '<')){
                $node = $config->getNode('frontend/layout/updates');
                unset($node->wishlist);
            }

            //example - remove rewrite a block that has created a third-party module
            //$node = $config->getNode('global/blocks/catalog/rewrite');
            //unset($node->layer_view);
        }
    }

    protected function _redirectToStore($urlRefferer = '', $switchTo = null){
        // generate redirect url
        $params = array(
            '_current' => TRUE,
            '_use_rewrite' => TRUE,
            '_store_to_url' => FALSE,
            '_secure' => Mage::app()->getStore(true)->isCurrentlySecure(),
            '_nosid' => TRUE,
            '_store' => Mage::helper('aw_mobile3/config')->getStoreMobileRedirectToSubdomain(),
        );

        if(!is_null($switchTo)){
            $params['_query'] = array(self::SWITCH_TO_PARAM => $switchTo);
        }

        if(!empty($urlRefferer)){
            $baseUrl = preg_replace(array('/https?:\/\/|www./', '/\/index.php/'), '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
            $newUrl = preg_replace(array('/https?:\/\/|www./', '/\/index.php/'), '', $urlRefferer);
            $urlRefferer = trim(str_replace($baseUrl, '', $newUrl), '/');
        }

        $url = Mage::getUrl($urlRefferer, $params);

        $session = Mage::getSingleton('customer/session');
        $session->setLanguageChecked(TRUE);
        Mage::app()->getFrontController()->getResponse()->setRedirect($url)->sendResponse();
    }

    public function predispatch()
    {
        if (!Mage::helper('aw_mobile3/config')->isEnabled()) {
            return $this;
        }

        $request = Mage::app()->getFrontController()->getRequest();

        if ($switchTo = $request->getParam(self::SWITCH_TO_PARAM, false)) {
            $requestUri = $_SERVER['REQUEST_URI'];
            $requestUri = str_replace(self::SWITCH_TO_PARAM.'='.$switchTo, '', $requestUri);
            $requestUri = substr_replace($requestUri, '', -1);

            switch($switchTo){
                case 'toDesktop':
                    Mage::getSingleton('core/cookie')->delete(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, '/', Mage::helper('aw_mobile3')->getDomainName());
                    Mage::getSingleton('core/cookie')->set(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, AW_Mobile3_Helper_Data::DESKTOP_VERSION_COOKIE_NAME, true, '/', Mage::helper('aw_mobile3')->getDomainName());
                    break;
                case 'toMobile':
                    Mage::getSingleton('core/cookie')->delete(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, '/', Mage::helper('aw_mobile3')->getDomainName());
                    Mage::getSingleton('core/cookie')->set(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, AW_Mobile3_Helper_Data::MOBILE_VERSION_COOKIE_NAME, true, '/', Mage::helper('aw_mobile3')->getDomainName());
                    break;
            }
            Mage::app()->getFrontController()->getResponse()->setRedirect($requestUri)->sendResponse();
            exit;
        }

        $urlRefferer = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl(Mage::app()->getRequest()->getRequestUri());
        //if on mobile store set desktop themes cookies. to avoid loops redirects
        if(Mage::helper('aw_mobile3/config')->isMobileRedirectToSubdomain() && Mage::helper('aw_mobile3/config')->isMobileDetection() == AW_Mobile3_Model_Source_Detection::FORCE_MOBILE_VALUE){
            $switcherCookie = Mage::getSingleton('core/cookie')->get(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME);
            //removed desktop themes cookies
            if($switcherCookie && $switcherCookie == AW_Mobile3_Helper_Data::DESKTOP_VERSION_COOKIE_NAME){
                Mage::getSingleton('core/cookie')->delete(AW_Mobile3_Helper_Data::MOBILE_COOKIE_NAME, '/', Mage::helper('aw_mobile3')->getDomainName());
                Mage::app()->getFrontController()->getResponse()->setRedirect($urlRefferer)->sendResponse();
                exit;
            }
        }

        //intercept native iPhone Theme redirects
        if(Mage::helper('aw_mobile3/config')->isMobileRedirectToSubdomain()){
            if($request->getModuleName() == 'awmobile3' && $request->getControllerName() == 'switch'){
                switch($request->getActionName()){
                    case 'toDesktop':
                        $this->_redirectToStore($urlRefferer, 'toDesktop');
                        exit;
                    case 'toMobile':
                        $this->_redirectToStore($urlRefferer, 'toMobile');
                        exit;
                }
            }
        }

        //auto switcher
        if(Mage::helper('aw_mobile3/config')->isMobileRedirectToSubdomain() && Mage::helper('aw_mobile3')->isCanShowMobileVersion()){
            //if this mobile store view then not send redirect
            if(Mage::helper('aw_mobile3/config')->isMobileDetection() == AW_Mobile3_Model_Source_Detection::FORCE_MOBILE_VALUE){
                return $this;
            }

            $this->_redirectToStore();
            exit;
        }
    }

    public function beforeRenderLayout()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (!$request->getParam(self::TRIGGER_REQUEST_PARAM, false)) {
            return $this;
        }
        unset($_GET[self::TRIGGER_REQUEST_PARAM]);
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = str_replace(self::TRIGGER_REQUEST_PARAM . '=true', '', $requestUri);
        $_SERVER['REQUEST_URI'] = substr($requestUri, -1) == "&" ?
            substr_replace($requestUri, '', -1) : preg_replace('/&{2,}/',"&",$requestUri);

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
        $this->_checkRedirects();
        $this->_checkHomePage();
        return $this;
    }

    protected function _checkHomePage()
    {
        if ((!Mage::helper('aw_mobile3')->isIphoneTheme() && !Mage::helper('aw_mobile3')->isIPadTheme())
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
        $mobileHomePageId = Mage::helper('aw_mobile3/config')->getIPhoneHomePageId();
        if (Mage::helper('aw_mobile3')->isIPadTheme()) {
            $mobileHomePageId = Mage::helper('aw_mobile3/config')->getIPadHomePageId();
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
        if (!Mage::helper('aw_mobile3')->isIphoneTheme()) {
            return $this;
        }
        $customerActions = array(
            'customer/account/login',
            'customer/account/logoutsuccess',
            'customer/account/create',
            'customer/account/forgotpassword',
            'aw_mobile3/customer/create',
            'aw_mobile3/customer/index',
            'aw_mobile3/customer/login',
            'aw_mobile3/customer/logoutsuccess',
            'aw_mobile3/customer/forgotpassword',
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
        $excludedModules = array(
            'blog',
        );
        if (in_array(Mage::app()->getRequest()->getModuleName(), $excludedModules)) {
            $this->_sendRedirectResponse(Mage::getUrl());
        }
        return $this;
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
        //init product list
        $productListBlock = $layout->getBlock('product_list');
        if (!$productListBlock) {
            $productListBlock = $layout->getBlock('search_result_list');
        }
        if (!$productListBlock) {
            $productListBlock = $layout->getBlock('search_result_catalog');
        }
        if ($productListBlock) {
            $content = $productListBlock->toHtml();
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
            $formType = Mage::app()->getRequest()->getPost('form_type');
            if($formType === 'checkout_login') {
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
        if ((!Mage::helper('aw_mobile3')->isIphoneTheme() && !Mage::helper('aw_mobile3')->isIPadTheme())
        ) {
            return $this;
        }
        $secureControllers = array(
            'checkout/onepage',
            'checkout/multishipping',
            'customer/account',
            'aw_mobile3/customer'
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
    ** This observer method is called prior to saving shipping method. We have added the observer event in the config.xml. Mohin, 26 Oct 2015 for Mobile 2
    ** We have added this code again for updating mobile theme 2.0.6 to 3.0.3. Erik, 11 Nov 2016, mantis-400
    */
    public function removeNonShippableProducts(Varien_Event_Observer $observer)
    {
        Mage::helper('restrictcountry')->removeNonShippableProducts();
    }
}
