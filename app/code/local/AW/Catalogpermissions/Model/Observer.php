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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Catalogpermissions_Model_Observer extends AW_Catalogpermissions_Model_Observer_Abstract
{
    /**
     * @var int
     */
    public static $i;

    /**
     * @var array
     */
    public static $temp = array();

    /**
     * @var bool
     */
    public static $useCache = false;

    /**
     * Predespatch event
     * At this point we load from cache or get from database info about disabled products
     * and categories and register them in global scope (Mage::registry) for later use
     * Load disabled products and categories from cache or on the fly
     */
    public function controllerActionPredispatch()
    {
        parent::prepareRewrites();
        if (!parent::_validateProcess()) {
            return;
        }

        Mage::helper('catalogpermissions')->clearWishlistCountInLinks();
        Mage::helper('catalogpermissions')->setCompareItemsCountInSession();

        /* Declare variables first as cache maybe removed manually */
        if (Mage::getModel('core/cache')->canUse(AW_Catalogpermissions_Model_Cache::CACHE_TYPE)) {
            self::$useCache = true;
        }

        Mage::helper('catalogpermissions/connection')->cacheDisabledCategories();
        /* Available in registry after by key AW_Catalogpermissions_Helper_Data::DIABLED_CATEGS_SCOPE */

        Mage::helper('catalogpermissions/connection')->cacheDisabledPriceProducts();
        /* Available in registry after by key AW_Catalogpermissions_Helper_Data::DISABLED_PRICE_PROD_SCOPE */

        /* Deprecated */
        Mage::helper('catalogpermissions/connection')->cacheDisabledPriceProducts();
        /* Available in registry after by key AW_Catalogpermissions_Helper_Data::DISABLED_PROD_SCOPE */
    }

    /**
     *  catalogProductCollectionLoadBefore
     *  preparePriceSelect
     *  Functions of this block [Block 1] are for dealing with product collection filters
     *
     * Event - catalog_product_collection_load_before
     *
     * @param Varien_Event_Observer $event
     */
    public function catalogProductCollectionLoadBefore($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        Mage::helper('catalogpermissions/connection')->addDisabledAttrToFilter($event->getData('collection'));
        parent::regroupProductCollection($event->getData('collection'));

    }

    /**
     * Check access to category and redirect if access denied
     *
     * @param Varien_Event_Observer $event
     */
    public function categoryControllerBeforeInit($event)
    {
        /** @var Mage_Catalog_CategoryController $categoryController */
        $categoryController = $event->getData('controller_action');
        $disabled_categories = Mage::helper('catalogpermissions/connection')->cacheDisabledCategories();
        $cat_id = (int)$categoryController->getRequest()->getParam('id', false);
        if ($disabled_categories and $cat_id and in_array($cat_id, $disabled_categories)) {
            $store_id = Mage::app()->getStore()->getId();
            $url = strip_tags(Mage::getStoreConfig('catalogpermissions/general/redirect_from_category', $store_id));
            if (strpos($url, 'http') === false) {
                $url = Mage::getBaseUrl() . $url;
            }
            $notice = Mage::getStoreConfig('catalogpermissions/general/redirect_from_category_notice', $store_id);
            if ($notice) {
                Mage::getSingleton('core/session')->addNotice($notice);
            }
            $categoryController->getResponse()->setRedirect($url);
            $categoryController->getResponse()->sendResponse();
            exit;
        }
    }

    /**
     *
     * This event is for dealing with products in the wishlist
     * Event - abstract_collection_load_before
     *
     * @param Varien_Event_Observer $event
     *
     */
    public function abstractCollectionBeforeLoad($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        $collection = $event->getData('collection');
        if ($collection instanceof Mage_Wishlist_Model_Mysql4_Item_Collection
            || $collection instanceof Mage_Wishlist_Model_Resource_Item_Collection
        ) {
            Mage::helper('catalogpermissions/connection')->addDisabledAttrToFilter(
                $event->getData('collection'), 'product_id', array('noPriceFilter' => true)
            );
        }
        /**
         * Don't delete disabled products if they are already in cart
         */
        if ($collection instanceof Mage_Sales_Model_Mysql4_Quote_Item_Collection) {
            AW_Catalogpermissions_Helper_Connection::$_lock = true;
        }
    }

    /**
     * Event - catalog_product_collection_apply_limitations_after
     *
     * @param Varien_Event_Observer $event
     */
    public function applyCatalogLimitations($event)
    {
        $this->catalogProductCollectionLoadBefore($event);
    }

    /*End of [Block 1] */

    /**
     *
     * catalogCategoryCollectionLoadBefore
     * pageTree
     * These functions are for dealing with category disabling process
     *
     * Event - catalog_category_collection_load_before
     *
     * @param Varien_Event_Observer $event
     *
     */
    public function catalogCategoryCollectionLoadBefore($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        Mage::helper('catalogpermissions/connection')->addDisCategoryAttrFilter($event->getData('category_collection'));

    }

    /**
     * Event - catalog_category_tree_init_inactive_category_ids
     *
     * @param Varien_Event_Observer $event
     */
    public function pageTree($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        $category_tree = $event->getData('tree');
        $event_name = $event->getData('event')->getData('name');
        if (!$category_tree) {
            return;
        }
        if ($event_name == 'catalog_category_tree_init_inactive_category_ids') {
            Mage::helper('catalogpermissions/connection')->addInactiveCategories($category_tree);
        }
    }

    /****************************************************************************************************************/
    /**
     * Block [Block 3] of methods below observe blocks and add specific info to product data $Product->setAWDisableOutOfStock(true);
     * Event - core_block_abstract_to_html_before
     *
     * @param Varien_Event_Observer $event
     */
    public function blockAbstractToHtmlBefore($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        if ($product = $event->getData('block')->getProduct()) {
            /** @var Mage_Catalog_Model_Product $product */
            $class = get_class($event->getData('block'));
            if ($class == 'Mage_Wishlist_Block_Render_Item_Price' || $class == 'Mage_Catalog_Block_Product_Price'
                || $class == 'Mage_Bundle_Block_Catalog_Product_Price'
                || $class == 'AW_Sarp_Block_Catalog_Product_Price'
                || $class == 'AW_Sarp_Block_Product_Price'
            ) {
                $disabled = Mage::registry(AW_Catalogpermissions_Helper_Data::DISABLED_PRICE_PROD_SCOPE);
                $disabled || $disabled = array();
                $isDisabledPrice = true;
                if ($product->getTypeId() === Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                    $prices = array();
                    foreach ($product->getTypeInstance()->getAssociatedProducts() as $child) {
                        /** @var Mage_Catalog_Model_Product $child */
                        if (!in_array($child->getId(), $disabled)) {
                            $prices[] = $child->getFinalPrice();
                        }
                    }
                    $product->setData('minimal_price', sizeof($prices) ? min($prices) : false);
                    $isDisabledPrice = (bool)$product->getData('minimal_price');
                }
                $currentGroupId = AW_Catalogpermissions_Helper_Data::getCustomerGroup();
                $globalRestrictedGroups = AW_Catalogpermissions_Helper_Data::getHidePriceGroupConfig();
                $isDisabledPrice = (($isDisabledPrice && in_array($product->getId(), $disabled))
                    || in_array(
                        $currentGroupId, $globalRestrictedGroups
                    ));
                if ($isDisabledPrice) {
                    AW_Catalogpermissions_Helper_Data::setVisibility($product, false);
                    self::$temp[
                    "aw_catalogpermissions_block_{$product->getId()}_" . get_class($event->getData('block'))]
                        = 1;
                }
            }
        }
    }

    /**
     *
     * Reset block price html if price attribute is selected
     *
     * @param Varien_Event_Observer $event
     *
     */
    public function blockAbstractToHtmlAfter($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        if (version_compare(Mage::getVersion(), '1.3.3.0', '<=')) {
            return;
        }
        if ($product = $event->getData('block')->getProduct()) {
            /** @var Mage_Catalog_Model_Product $product */
            if (isset(self::$temp[
            "aw_catalogpermissions_block_{$product->getId()}_" . get_class($event->getData('block'))])
            ) {
                $event->getData('transport')->setHtml('');
            }
        }
    }

    /**
     * @param Varien_Event_Observer $event
     *
     * @return bool True if access to product denied
     */
    public function catalogProductisSalableBefore($event)
    {
        if (!parent::_validateProcess()) {
            return true;
        }
        return $this->_helper->checkVisibility($event->getData('product'), false);
    }

    /**
     *  Event - catalog_controller_product_view
     *  Redirect to catalogProductisSalableBefore($Event)
     *
     * @param Varien_Event_Observer $event
     */
    public function catalogProductView($event)
    {
        $result = $this->_helper->checkVisibility($event->getData('product'), false, array('Product'));
        if ($result) {
            $response = Mage::app()->getResponse();
            $store_id = Mage::app()->getStore()->getId();
            $url = strip_tags(Mage::getStoreConfig('catalogpermissions/general/redirect_from_product', $store_id));
            if (strpos($url, 'http') === false) {
                $url = Mage::getBaseUrl() . $url;
            }
            $notice = Mage::getStoreConfig('catalogpermissions/general/redirect_from_product_notice', $store_id);
            if ($notice) {
                Mage::getSingleton('core/session')->addNotice($notice);
            }
            $response->setRedirect($url);
            $response->sendResponse();
            exit;
        }
        $this->catalogProductisSalableBefore($event);
    }

    /**
     * Event - review_controller_product_init
     * Redirect to catalogProductisSalableBefore($Event)
     *
     * @param Varien_Event_Observer $event
     */
    public function catalogProductReview($event)
    {
        $this->catalogProductisSalableBefore($event);
    }

    /* End of [Block 3] */

    public function wishlistItemsRenewed()
    {
        if (!parent::_validateProcess()) {
            return;
        }
        $_wishlistHelper = Mage::helper('wishlist');
        if (method_exists($_wishlistHelper, 'getWishlistItemCollection')) {
            $collection = Mage::helper('wishlist')->getWishlistItemCollection();
            $_switch = true;
        } else {
            if (method_exists($_wishlistHelper, 'getItemCollection')) {
                $collection = Mage::helper('wishlist')->getItemCollection();
                $_switch = false;
            } else {
                return;
            }
        }
        Mage::helper('catalogpermissions/connection')->addDisabledAttrToFilter(
            $collection, 'product_id', array('noPriceFilter' => true)
        );
        /* Provide compatibility with CE versions 1.5.1.0 and older */
        if (!$_switch) {
            $count = $collection->count();
            Mage::getSingleton('customer/session')->setWishlistItemCount($count);
            return;
        }
        if (Mage::getStoreConfig(Mage_Wishlist_Helper_Data::XML_PATH_WISHLIST_LINK_USE_QTY)) {
            $count = $collection->getItemsQty();
        } else {
            $count = $collection->count();
        }
        Mage::getSingleton('customer/session')->setWishlistItemCount($count);
    }

    /**
     *
     * Fix for Magento EE versions 1.10.0.0 >= to be and CE >= 1.6.1.0
     * compatible with wishlist
     * According to Magento filters not salable products are not displayed in wishlist
     * At the moment of check our products are salable, but after there are not
     *
     */
    public function wishlistAfterLoad($Event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        if (Mage::helper('awall/versions')->getPlatform() === AW_All_Helper_Versions::CE_PLATFORM) {
            if (!version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
                return;
            }
        }
        self::$i++;
        foreach ($Event->getProductCollection() as $product) {
            if ($this->_helper->checkVisibility($product, true, array('Price'))) {
                Mage::register('aw_catalogpermissions_salable' . $product->getId(), $product->getId(), true);
                $product->isSalable();
            }
        }
    }

    /**
     * @param Varien_Event_Observer $event
     */
    public static function salableAfter($event)
    {
        if (!parent::_validateProcess()) {
            return;
        }
        if (Mage::app()->getRequest()->getRequestString() == '/wishlist/index/allcart/') {
            return;
        }
        $module = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();

        $fullPath = "{$module}/{$controller}/{$action}";
        $incr = $fullPath != 'wishlist/index/index' ? 2 : 4;

        /** @var Mage_Catalog_Model_Product $product */
        $product = $event->getData('product');
        $key = 'aw_catalogpermissions_salable' . $product->getId();
        $base = 'aw_catalogpermissions_salable' . $product->getId() . 'base';

        if (self::$i == 2 && $incr == 2) {
            Mage::unregister($base);
            self::$i = 1;
        }

        if (Mage::registry($key) !== null && Mage::registry($base) === null && $product->getStatus() != '2') {
            $salable = $event->getSalable();
            $salable->setIsSalable(true);
            Mage::register($base, ($product->getId() + 1), true);
        }
        $pass = ((Mage::registry($key) + $incr) < Mage::registry($base));
        if (Mage::registry($key) !== null && Mage::registry($base) !== null && !$pass) {
            $salable = $event->getSalable();
            $salable->setIsSalable(true);
            $old = Mage::registry($base);
            Mage::unregister($base);
            Mage::register($base, ($old + 1), true);
        } else {
            Mage::unregister($base);
        }

        if (Mage::helper('catalogpermissions')->checkVisibility($product, false)) {
            $salable = $event->getSalable();
            $salable->setIsSalable(false);
        }
    }

    /**
     * @return AW_Catalogpermissions_Helper_Connection
     */
    protected function _getConnectionHelper()
    {
        return Mage::helper('catalogpermissions/connection');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkBlock($observer)
    {
        $block = $observer->getData('block');
        if ($block instanceof Mage_Catalog_Block_Product_View) {
            $this->_checkProductViewBlock($block);
        } elseif ($block instanceof Mage_Catalog_Block_Category_View) {
            $this->_checkCategoryViewBlock($block);
        }
    }

    protected function _checkCategoryViewBlock(Mage_Catalog_Block_Category_View $block)
    {
        $categoryId = $block->getCurrentCategory()->getId();
        if ($this->_getConnectionHelper()->isCategoryDisabled($categoryId)) {
            $block->getLayout()->getMessagesBlock()->addNotice(
                Mage::helper('catalog')->__('You do not have permission to access this page')
            );
            $block->setTemplate('catalog/category/empty.phtml');
        }
    }

    protected function _checkProductViewBlock(Mage_Catalog_Block_Product_View $block)
    {
        /** @var Mage_Catalog_Model_Product */
        $product = $block->getProduct();
        $productId = $product->getId();
        $categoryId = $product->getCategoryId();
        $_canShow = true;
        if (!$categoryId && ($categoryIds = $block->getProduct()->getCategoryIds())) {
            $_canShow = false;
            foreach ($categoryIds as $_categoryId) {
                if (!$this->_getConnectionHelper()->isCategoryDisabled($_categoryId)) {
                    $_canShow = true;
                    break;
                }
            }
        }
        if ($this->_getConnectionHelper()->isDisabled($productId)
            || ($categoryId && $this->_getConnectionHelper()->isCategoryDisabled($categoryId))
            || (!$categoryId && !$_canShow)
        ) {
            $block->getLayout()->getMessagesBlock()->addNotice(
                Mage::helper('catalog')->__('You do not have permission to access this page')
            );
            $block->setTemplate('catalog/product/empty.phtml');
        }
    }
}
