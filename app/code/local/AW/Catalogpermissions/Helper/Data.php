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


class AW_Catalogpermissions_Helper_Data extends Mage_Core_Helper_Abstract
{
	const PRICE_RESTRICTED_TEXT = '';

	const CP_DISABLE_PRODUCT = 'aw_cp_disable_product';

	const CP_DISABLE_PRICE = 'aw_cp_disable_price';

	const CP_DISABLE_CATEGORY = 'aw_cp_categorydisable';

	const NOT_LOGGED_IN_STATUS = '-1';

	/*
	 * Scope to store disabled products
	 */
	const DISABLED_PROD_SCOPE = 'aw_cp_disabled_products';

	/*
	 * Scope to store products from excluded categories
	 * In cache mode this scope also includes DISABLED_PROD_SCOPE
	 */
	const EXCLUDED_PROD_SCOPE = 'aw_cp_excluded_products';

	/*
	 * Scope to store products with disabled price
	 */
	const DISABLED_PRICE_PROD_SCOPE = 'aw_cp_disabled_price_products';

	/**
	 * Scope to store disabled categories
	 */
	const DIABLED_CATEGS_SCOPE = 'aw_cp_excluded_cats';


	/**
	 * Dynamically check attributes for product passed in $mode var
	 * If found (depending on flag) set out of stock flag, salable flag to false,
	 * and return bool or product
	 *
	 * @param Mage_Catalog_Model_Product $Product
	 * @param bool                       $flag
	 * @param array                      $mode
	 *
	 * @return bool
	 */

	public static function checkVisibility($Product, $flag = true, $mode = array('Price', 'Product'))
	{
		$Product->load($Product->getId());
		$customerGroupId = self::getCustomerGroup();

		foreach ($mode as $mod) {
			$method = "getAwCpDisable{$mod}";
			$restrictedGroups = self::getRestrictedGroupForProduct($Product, $method);
			if (array_search($customerGroupId, $restrictedGroups) !== false) {
				if ($flag) {
					$Product->setData('a_wdisable_out_of_stock', true);
					return true;
				} else {
					$Product->setData('is_salable', false);
					$Product->setData('a_wdisable_out_of_stock', true);
					return true;
				}
			}
		}
		return false;
	}

	public static function getHidePriceGroupConfig($storeId = null)
	{
		$restrictedGroups = array();
		$groups = Mage::getStoreConfig('catalogpermissions/general/hide_price_for_group', $storeId);
		if ($groups) {
			$restrictedGroups = explode(',', $groups);
		}
		return $restrictedGroups;
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @param string                     $method
	 *
	 * @return array
	 */
	public static function getRestrictedGroupForProduct($product, $method)
	{
		$extraRestrictedGroups = array();
		if ($method === 'getAwCpDisablePrice') {
			$extraRestrictedGroups = self::getHidePriceGroupConfig();
		}
		if ($product->getData('type_id') === Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE
			&& $method === 'getAwCpDisablePrice'
		) {
			$priceDisabledProducts = Mage::registry(self::DISABLED_PRICE_PROD_SCOPE);
			/** Return if not products with disabled price */
			if ($priceDisabledProducts) {
				$result = array();
				$children = $product->getTypeInstance()->getAssociatedProducts();
				foreach ($children as $child) {
					$result[] = in_array($child->getId(), $priceDisabledProducts);
				}
				/** If grouped product haven't any product without disabled price */
				if (!in_array(false, $result)) {
					return array(self::getCustomerGroup());
				}
			}
		}

		$restrictedGroups = $product->{$method}();
		if ($restrictedGroups) {
			$restrictedGroups = explode(",", $restrictedGroups);
			return array_unique(array_merge($extraRestrictedGroups, $restrictedGroups));
		}
		return $extraRestrictedGroups;
	}

	/**
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param bool                       $flag
	 *
	 * @return bool | Mage_Catalog_Model_Product
	 */
	public static function setVisibility($product, $flag = true)
	{
		if ($flag) {
			$product->setAWDisableOutOfStock(true);
			return true;
		} else {
			$product->setIsSalable(false);
			$product->setAWDisableOutOfStock(true);
			return $product;
		}
	}

	/**
	 *  This function is called from preDisapath method and dynamically rewrites
	 *  config elements depending on Magento version and specific settings
	 *
	 */
	public static function prepareRewrites()
	{

		/* Add specific rewrites if tree like sitemap is enabled */
		if (Mage::getStoreConfig('catalog/sitemap/tree_mode')) {
			$catalogRewrites = Mage::getConfig()->getNode('global/blocks/catalog/rewrite');
			$treeRewrites = Mage::getConfig()->getNode('global/blocks/catalog/catalogpermissions_treesitemap');
			foreach ($treeRewrites->children() as $dnode) {
				$catalogRewrites->appendChild($dnode);
			}
		}

		/* Return native blocks classes back if module output is disabled */
		if (Mage::getStoreConfig('advanced/modules_disable_output/AW_Catalogpermissions')) {
			Mage::getConfig()->setNode(
				'global/blocks/catalog/rewrite/seo_sitemap_tree_category',
				'Mage_Catalog_Block_Seo_Sitemap_Tree_Category', true
			);
			Mage::getConfig()->setNode(
				'global/blocks/catalog/rewrite/seo_sitemap_tree_pager', 'Mage_Catalog_Block_Seo_Sitemap_Tree_Pager',
				true
			);
		}
	}

	public function clearWishlistCountInLinks()
	{

		Mage::getSingleton('customer/session')->unsWishlistItemCount();

	}

	/**
	 * Recalculate compared items count
	 */
	public function setCompareItemsCountInSession()
	{
		Mage::helper('catalog/product_compare')->calculate();
	}

	public static function _getPathInfo($sep = "_", $full = 'short')
	{

		$request = Mage::app()->getRequest();

		if ($full == 'short') {
			return "{$request->getControllerName()}$sep{$request->getActionName()}";
		} else {
			return "{$request->getModuleName()}$sep{$request->getControllerName()}$sep{$request->getActionName()}";
		}
	}

	public static function _isSortByPrice()
	{
		$requestPrice = Mage::app()->getRequest()->getParam('price');
		if (preg_match('#^\d*,\d*$#is', $requestPrice) || preg_match('#^\d*%\d*$#is', $requestPrice)) {
			return true;
		}
		return false;
	}

	public static function checkProductAvailability($hiddenProducts = array(), $requestItem = 'product')
	{

		$productId = (int)Mage::app()->getRequest()->getParam('product');
		$product = Mage::getModel('catalog/product')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($productId);

		if ($product) {
			if (self::checkVisibility($product, true)) {
				Mage::app()->getRequest()->setParam($requestItem, false);
				Mage::getSingleton('checkout/session')->addNotice(
					Mage::helper('catalogpermissions')->__("%s is not for sale", $product->getName())
				);
				unset($product);
			}
			if (array_search($productId, $hiddenProducts) !== false) {
				Mage::app()->getRequest()->setParam($requestItem, false);
				unset($product);
				Mage::getSingleton('checkout/session')->addNotice("Product not found");
			}
		}


		/* Prevent adding from grouped product */
		$superGroup = Mage::app()->getRequest()->getParam('super_group');

		if (is_array($superGroup)) {
			if (count($superGroup) > 0) {

				$superGroup = array_keys($superGroup);

				foreach ($superGroup as $productId) {

					$product = Mage::getModel('catalog/product')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($productId);
					if ($product) {
						if (self::checkVisibility($product, true)) {
							unset($_POST['super_group'][$productId]);
							Mage::getSingleton('checkout/session')->addNotice(
								Mage::helper('catalogpermissions')->__("%s is not for sale", $product->getName())
							);
						}
					}
				}
			}
		}

		/* Prevent adding as a part of configurable product */
		$superGroup = Mage::app()->getRequest()->getParam('super_attribute');

		if (is_array($superGroup)) {
			if (count($superGroup) > 0) {

				foreach ($superGroup as $productId) {

					$product = Mage::getModel('catalog/product')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($productId);
					if ($product) {

						if (self::checkVisibility($product, true)) {
							unset($_POST['super_attribute'][$productId]);
							Mage::getSingleton('checkout/session')->addNotice(
								Mage::helper('catalogpermissions')->__("%s is not for sale", $product->getName())
							);
						}
					}
				}
			}
		}

		/* Prevent adding as a part of configurable option */
		$superGroup = Mage::app()->getRequest()->getParam('super_attribute');

		if (is_array($superGroup)) {
			if (count($superGroup) > 0) {
				foreach ($superGroup as $productId) {

					$product = Mage::app()->getRequest()->getParam('product');

					$product = Mage::getModel('catalog/product')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($product);

					if ($product->isConfigurable()) { // Matthew : Fatal error: Call to undefined method OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple::getProductByAttributes()
						$subProduct = $product->getTypeInstance(true)->getProductByAttributes(
							array(key($superGroup) => $productId), $product
						);

						if ($subProduct) {
							if (self::checkVisibility($subProduct, true)) {
								unset($_POST['super_attribute'][key($superGroup)]);
								Mage::getSingleton('checkout/session')->addNotice(
									Mage::helper('catalogpermissions')->__("%s is not for sale", $subProduct->getName())
								);
							}
						}
					}
				}
			}
		}
	}

	public static function checkRecentOrdersAvailability($hiddenProducts = array())
	{

		$orderItemIds = Mage::app()->getRequest()->getParam('order_items', array());

		if (!is_array($orderItemIds)) {
			return;
		}
		$itemsCollection = Mage::getModel('sales/order_item')->getCollection()->addIdFilter($orderItemIds)->load();

		$productIds = array();
		foreach ($itemsCollection as $item) {
			$product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load(
				$item->getProductId()
			);
			if ($product) {
				if (self::checkVisibility($product, true)) {
					$productIds[] = $item->getItemId();
					Mage::getSingleton('checkout/session')->addNotice(
						Mage::helper('catalogpermissions')->__("%s is not for sale", $item->getName())
					);
				}
				if (array_search($item->getProductId(), $hiddenProducts) !== false) {
					$productIds[] = $item->getItemId();
					Mage::getSingleton('checkout/session')->addNotice("Product not found");
				}
			}
		}
		Mage::app()->getRequest()->setPost('order_items', array_diff($orderItemIds, $productIds));
	}

	/**
	 * Deprecated since 1.2
	 *
	 * @deprecated
	 *
	 * @param type $ProductCollection
	 * @param type $customerGroupId
	 * @param type $forcePrice
	 *
	 * @return type
	 *
	 */

	public static function getDisabledFlatEntities($ProductCollection, $customerGroupId, $forcePrice = false)
	{

		$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
		$disPro = $eavAttribute->getIdByCode('catalog_product', self::CP_DISABLE_PRODUCT);
		$disPri = $eavAttribute->getIdByCode('catalog_product', self::CP_DISABLE_PRICE);


		if ($ProductCollection) {
			@$ProductCollection->addAttributeToSelect(self::CP_DISABLE_PRODUCT);
			@$ProductCollection->addAttributeToSelect(self::CP_DISABLE_PRICE);
		}

		$resource = Mage::getSingleton('core/resource');
		$db = $resource->getConnection('core_read');
		$tableName = $resource->getTableName('catalog/product') . "_text";


		if (Mage::app()->getRequest()->getModuleName() == 'rss' || self::_isSortByPrice()) {
			$select = $db->select()
				->from(array('attr' => $tableName), array('entity_id'))
				->where('attr.attribute_id IN (?)', array($disPro, $disPri))
				->where('attr.value != 0')
				->where("attr.value REGEXP '(^|,)" . $customerGroupId . "(,|$)'")
				->where('attr.store_id in (?)', array(0, Mage::app()->getStore()->getId()));
		} else {
			if ($forcePrice) {
				$select = $db->select()
					->from(array('attr' => $tableName), array('entity_id'))
					->where('attr.attribute_id IN (?)', array($disPri))
					->where('attr.value != 0')
					->where("attr.value REGEXP '(^|,)" . $customerGroupId . "(,|$)'")
					->where('attr.store_id in (?)', array(0, Mage::app()->getStore()->getId()));
			} else {
				$select = $db->select()
					->from(array('attr' => $tableName), array('entity_id'))
					->where('attr.attribute_id = ?', $disPro)
					->where('attr.value != 0')
					->where("attr.value REGEXP '(^|,)" . $customerGroupId . "(,|$)'")
					->where('attr.store_id in (?)', array(0, Mage::app()->getStore()->getId()));
			}
		}

		$where = array_unique($db->fetchCol($select));
		return $where;
	}

	/**
	 * Deprecated since 1.2
	 *
	 * @deprecated
	 *
	 * @param int $customerGroupId
	 *
	 * @return array
	 *
	 */
	public static function getDisabledCategories($customerGroupId)
	{

		$categoryCollection = Mage::getModel('catalog/category')->getCollection();
		$categoryCollection->addAttributeToSelect(self::CP_DISABLE_PRODUCT);

		$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
		$code = $eavAttribute->getIdByCode('catalog_category', self::CP_DISABLE_CATEGORY);

		$resource = Mage::getSingleton('core/resource');
		$db = $resource->getConnection('core_read');

		$tableName = $resource->getTableName('catalog/category') . "_text";

		$select = $db->select()
			->from(array('attr' => $tableName), array('entity_id'))
			->where('attr.attribute_id = ?', $code)
			->where('attr.value != 0')
			->where("attr.value REGEXP '(^|,)" . $customerGroupId . "(,|$)'")
			->where('attr.store_id in (?)', array(0, Mage::app()->getStore()->getId()));

		$categories = array_unique($db->fetchCol($select));

		$disCats = array();
		$temp = array();
		$hash = array();
		$catPro = array();
		foreach ($categories as $category) {
			$hash = array_merge($hash, $temp);
			$temp = explode(",", Mage::getModel('catalog/category')->load($category)->getAllChildren());

			if (!in_array($category, $hash)) {
				$catPro[] = $category;
			}
			$disCats = array_merge($disCats, $temp);
		}
		$disCats = array_unique($disCats);
		return array('scope' => $disCats, 'unique' => $catPro);
	}

	/**
	 *
	 * @return int
	 */
	public static function getCustomerGroup()
	{
		$customer = Mage::getSingleton('customer/session');
		return $customer->isLoggedIn() ? $customer->getCustomer()->getGroupId() : self::NOT_LOGGED_IN_STATUS;
	}

	/**
	 * Deprecated since 1.2
	 *
	 * @deprecated
	 *
	 * @param array $categories
	 *
	 * @return array
	 *
	 */

	public static function getDisabledProducts($categories)
	{
		$productsToDisable = array();
		foreach ($categories as $cat) {
			$ids = Mage::getModel('catalog/category')->load($cat)->getProductCollection()->getAllIds();
			$productsToDisable = array_merge($productsToDisable, $ids);
		}
		return $productsToDisable;
	}
}
