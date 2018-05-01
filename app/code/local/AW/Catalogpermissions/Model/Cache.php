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


class AW_Catalogpermissions_Model_Cache extends Mage_Core_Model_Abstract
{

	const CACHE_TYPE = 'catalogpermissions';

	const CATEGORY_CACHE_TAG = 'CATEGORY';

	const PRODUCT_CACHE_TAG = 'PRODUCT';

	const DISABLED_PRODUCT_CACHE_TAG = 'DISABLED_PRODUCT';

	/**
	 * @var bool
	 */
	public $defaultStore = false;
	public $removeFromAll = false;
	protected $_rmAllFromPriceScope = false;
	protected $_rmAllFromProductScope = false;
	protected $_awCpDisablePrice = null;
	protected $_awCpDisableProduct = null;
	protected $_awCpDisableProductUseDefault = false;
	protected $_awCpDisablePriceUseDefault = false;


	/**
	 * @var obj
	 */
	protected $_product;


	/**
	 *
	 * @var object
	 */
	protected static $_instance;


	protected static $_cacheRefreshed = false;

	public function _construct()
	{

		parent::_construct();
		$this->_init('catalogpermissions/cache');
	}

	public function getCache()
	{

		return Mage::getModel('core/cache');
	}

	private function _initConnection()
	{

		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}

	protected function _getCustomerGroups()
	{

		if (!$this->getData('customer_groups')) {
			$notLoggedIn = AW_Catalogpermissions_Helper_Data::NOT_LOGGED_IN_STATUS;
			$customerGroups = Mage::getModel('customer/group')->getCollection()->getAllIds();
			array_unshift($customerGroups, $notLoggedIn);
			$this->setData('customer_groups', $customerGroups);
		}

		return $this->getData('customer_groups');
	}

	protected function _getCustomerGroupsFromRequest($key)
	{

		$allCustomerGroups = $this->_getCustomerGroups();
		$reqInfo = Mage::app()->getRequest()->getParam('product');

//        if (isset($reqInfo[$key])) { // default
		if (is_array($reqInfo) && isset($reqInfo[$key])) { // Matthew
			if ($this->_checkRemoveFromAll($reqInfo, $key)) {
				return $this->_getCustomerGroups();
			}
			return $reqInfo[$key];
		}

		return false;
	}

	private function _checkRemoveFromAll($request, $key)
	{

		if (!isset($request[$key][0])) {
			return false;
		}

		if ($request[$key][0] == 0) {
			if ($key == AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT) {
				if ($this->_product->getOrigData(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT) !== '0') {
					$this->_rmAllFromProductScope = true;
					return true;
				}
			} else {
				if ($key == AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE) {
					if ($this->_product->getOrigData(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE) !== '0') {
						$this->_rmAllFromPriceScope = true;
						return true;
					}
				}
			}
		}

		return false;
	}

	protected function _getStoresCollection()
	{

		if (!$this->getData('stores_collection')) {

			$storeIds = Mage::app()->getStore()->getCollection()->getAllIds();
			asort($storeIds);
			if (isset($storeIds)) {
				if ($storeIds[0] == 0) {
					array_shift($storeIds);
				}
			}

			$this->setData('stores_collection', $storeIds);
		}

		return $this->getData('stores_collection');
	}

	protected function _getStoresFromRequest()
	{

		if (!Mage::app()->getRequest()->getParam('store')) {
			$this->defaultStore = true;
			return $this->_getStoresCollection();
		}

		return (array)Mage::app()->getRequest()->getParam('store');
	}

	public function refreshCache()
	{

		//$start = microtime(true);

		/* Cache product ids with hidden price for every customerGroup x storeViews god help me */
		$scopeInfo = array();
		foreach ($this->_getCustomerGroups() as $group) {
			foreach ($this->_getStoresCollection() as $store) {

				$scopeInfo['storeId'] = $store;
				$scopeInfo['groupId'] = $group;

				$this->_cacheProductsWithHiddenPrice($scopeInfo);

				$this->_cacheDisabledCategories($scopeInfo);

			}
		}

		//Mage::getSingleton('adminhtml/session')->addSuccess(microtime(true) - $start);
	}

	protected function _cacheDisabledCategories(array $scope)
	{

		Mage::helper('catalogpermissions/connection')->cacheDisabledCategories($scope['storeId'], $scope['groupId']);

		$categories = Mage::registry(AW_Catalogpermissions_Helper_Data::DIABLED_CATEGS_SCOPE);

		Mage::unregister(AW_Catalogpermissions_Helper_Data::DIABLED_CATEGS_SCOPE);

		$_cacheTag = $this->_getCacheTag($scope['groupId'], $scope['storeId'], self::CATEGORY_CACHE_TAG);

		Mage::app()->removeCache($_cacheTag);
		if (!empty($categories)) {
			Mage::app()->saveCache(serialize($categories), $_cacheTag);
		}

	}

	protected function _cacheProductsWithHiddenPrice(array $scope)
	{

		Mage::helper('catalogpermissions/connection')->cacheDisabledPriceProducts($scope['storeId'], $scope['groupId']);

		$products = Mage::registry(AW_Catalogpermissions_Helper_Data::DISABLED_PRICE_PROD_SCOPE);

		Mage::unregister(AW_Catalogpermissions_Helper_Data::DISABLED_PRICE_PROD_SCOPE);

		$_cacheTag = $this->_getCacheTag($scope['groupId'], $scope['storeId'], self::PRODUCT_CACHE_TAG);

		Mage::app()->removeCache($_cacheTag);
		if (!empty($products)) {
			Mage::app()->saveCache(serialize($products), $_cacheTag);
		}
	}

	private function _getCacheTag($id, $storeId = 0, $type = self::CATEGORY_CACHE_TAG)
	{

		$cacheTag = self::CACHE_TYPE . "_{$type}_{$id}_{$storeId}";

		return strtoupper($cacheTag);
	}

	/**
	 * Static wrapper for private _getCacheTag
	 *
	 * @param string $customerGroup
	 * @param int    $storeId
	 * @param string $type
	 *
	 * @return string
	 *
	 */
	public static function getCacheTag($customerGroup, $storeId, $type)
	{

		if (!self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance->_getCacheTag($customerGroup, $storeId, $type);
	}


	public static function loadCache($_cacheTag)
	{

		if (!self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance->getCache()->load($_cacheTag);
	}


	public function deprecated__productSaveAfter($observer)
	{

		/* Cache for catalogpermissions is displayed */
		if (!Mage::getModel('core/cache')->canUse(self::CACHE_TYPE)) {
			return;
		}

		if (!self::$_cacheRefreshed) {
			$this->refreshCache();
			self::$_cacheRefreshed = true;
		}

	}

	/**
	 *  Refresh cache for category
	 */
	public function categoryPrepareSave($observer)
	{

		/* Cache for catalogpermissions is displayed */
		if (!Mage::getModel('core/cache')->canUse(self::CACHE_TYPE)) {
			return;
		}

		if (!self::$_cacheRefreshed) {
			$this->refreshCache();
			self::$_cacheRefreshed = true;
		}

	}

	/**
	 *  Refresh cache after massAttributesUpdate
	 */
	public function massAttributesUpdate($observer)
	{

		/* Cache for catalogpermissions is displayed */
		if (!Mage::getModel('core/cache')->canUse(self::CACHE_TYPE)) {
			return;
		}

		if (!self::$_cacheRefreshed) {
			$this->refreshCache();
			self::$_cacheRefreshed = true;
		}

	}


	public function productSaveAfter($observer)
	{

		/* Cache for catalogpermissions is displayed */
		if (!Mage::getModel('core/cache')->canUse(self::CACHE_TYPE)) {
			return;
		}

		// $start = microtime(true);

		$this->_product = $observer->getProduct();

		/* get eav attribute ids */
		$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
		$attrDisablePrice = $eavAttribute->getIdByCode(
			'catalog_product', AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE
		);
		$attrDisableProduct = $eavAttribute->getIdByCode(
			'catalog_product', AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT
		);
		/* Init connection */
		$connection = $this->_initConnection();

		/* Init tables */
		$catalogProductEntity = Mage::getSingleton('core/resource')->getTableName('catalog/product');
		$attributesProductTable = "{$catalogProductEntity}_text";

		$stores = $this->_getStoresFromRequest();


		/* We take customer groups from request just to make sure that is some kind of a product save process
		 * and not product order placing for ex.
		 */
		$disPrice = $this->_getCustomerGroupsFromRequest(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE);
		$disPro = $this->_getCustomerGroupsFromRequest(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT);

		$disAttrScope = array();
		$disAttrCodes = array();
		if (!empty($disPrice)) {
			$disAttrScope[self::PRODUCT_CACHE_TAG] = $disPrice;
			$disAttrCodes[self::PRODUCT_CACHE_TAG] = $attrDisablePrice;
		}
		if (!empty($disPro)) {
			$disAttrScope[self::DISABLED_PRODUCT_CACHE_TAG] = $disPro;
			$disAttrCodes[self::DISABLED_PRODUCT_CACHE_TAG] = $attrDisableProduct;
		}

		/* There are no values in request, when we use default settings but in this case
		 *  we have to clear store cache and add default cache using the same logic of array_diff
		 *
		 */
		$defaultValues = Mage::app()->getRequest()->getParam('use_default');

		if ($defaultValues && is_array($defaultValues)) {
			if (in_array(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE, $defaultValues)) {
				$this->_awCpDisablePriceUseDefault = true;
				$disAttrScope[self::PRODUCT_CACHE_TAG] = explode(
					',', $observer->getProduct()->getOrigData(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE)
				);
				$disAttrCodes[self::PRODUCT_CACHE_TAG] = $attrDisablePrice;
			}

			if (in_array(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT, $defaultValues)) {
				$this->_awCpDisableProductUseDefault = true;
				$disAttrScope[self::DISABLED_PRODUCT_CACHE_TAG] = explode(
					',', $observer->getProduct()->getOrigData(AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT)
				);
				$disAttrCodes[self::DISABLED_PRODUCT_CACHE_TAG] = $attrDisableProduct;
			}
		}

		$productId = $observer->getProduct()->getId();

		$this->_awCpDisablePrice = $observer->getProduct()->getOrigData(
			AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE
		); // this values were save before
		$this->_awCpDisableProduct = $observer->getProduct()->getOrigData(
			AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT
		);

		/* Process default store settings save process */

		foreach ($stores as $store) {
			foreach ($disAttrScope as $key => $attribute) {

				$this->_processDefaultAfterSave(
					array('entityTable' => $catalogProductEntity, 'attrTable' => $attributesProductTable), // tables
					array('productId' => $productId, 'storeId' => $store, 'cacheTag' => $key,
						  'attribute' => $attribute), // scope info
					$connection, // connection
					$disAttrCodes[$key]
				);
			}
		}

		// Mage::getSingleton('adminhtml/session')->addSuccess(microtime(true) - $start);
	}

	protected function _processDefaultAfterSave(array $tables, array $scope, $connection, $attrCode)
	{

		/* There is no need to do calculations if removeFromAll is selected */
		if ($scope['cacheTag'] == self::PRODUCT_CACHE_TAG && $this->_rmAllFromPriceScope === true) {
			$groupsToProcess = array('groups_to_add' => array(), 'groups_to_remove' => $scope['attribute']);
		} else {
			if ($scope['cacheTag'] == self::DISABLED_PRODUCT_CACHE_TAG && $this->_rmAllFromProductScope === true) {
				$groupsToProcess = array('groups_to_add' => array(), 'groups_to_remove' => $scope['attribute']);
			} else {

				$groupsToProcess = $this->_getGroupIdsForProcess($tables, $scope, $connection, $attrCode);
			}
		}

		if ($this->defaultStore) {
			$select = $connection->select()
				->reset()
				->from(array('_products' => $tables['entityTable']), array())
				->join(
					array('_store' => $tables['attrTable']),
					"_store.entity_id = _products.entity_id AND _store.attribute_id = {$attrCode} AND _store.store_id = {$scope['storeId']}",
					array('attrVal' => new Zend_Db_Expr('_store.value_id'))
				)
				->where("_store.value IS NOT NULL")
				->where("_products.entity_id = ?", $scope['productId']);
			$result = $connection->fetchCol($select);

			if (!empty($result)) {
				// If attribute use store settings
				return false;
			}
		}

		foreach ($groupsToProcess['groups_to_remove'] as $group) {
			$_cacheTag = $this->_getCacheTag($group, $scope['storeId'], $scope['cacheTag']);

			$cache = @unserialize(Mage::app()->loadCache($_cacheTag));

			if (is_array($cache) && !empty($cache)) {

				$key = array_search($scope['productId'], $cache);

				if ($key || $key === 0) {
					$key = (int)$key;
					unset($cache[$key]);
					Mage::app()->saveCache(serialize($cache), $_cacheTag);
				}
			}
		}


		foreach ($groupsToProcess['groups_to_add'] as $group) {

			$_cacheTag = $this->_getCacheTag($group, $scope['storeId'], $scope['cacheTag']);
			$cache = @unserialize(Mage::app()->loadCache($_cacheTag));

			if (!is_array($cache) || !$cache) {
				$cache = array();
			}

			if (!in_array($scope['productId'], $cache)) {
				$cache[] = $scope['productId'];
				Mage::app()->saveCache(serialize($cache), $_cacheTag);
			}
		}
	}

	protected function _isDefaultStore()
	{

		return $this->defaultStore;
	}

	protected function _getGroupIdsForProcess(array $tables, array $scope, $connection, $attrCode)
	{


		/* Prepare select */
		$select = $connection->select()
			->reset()
			->from(array('_products' => $tables['entityTable']), array())
			->join(
				array('_store' => $tables['attrTable']),
				"_store.entity_id = _products.entity_id AND _store.attribute_id = {$attrCode} AND _store.store_id = 0",
				array('attrVal' => new Zend_Db_Expr('_store.value'))
			)
			->where("_store.value IS NOT NULL")
			->where("_products.entity_id = ?", $scope['productId']);


		if ($scope['cacheTag'] == self::PRODUCT_CACHE_TAG) {

			if ($this->_awCpDisablePriceUseDefault) {


				$result = $connection->fetchCol($select);
				$result = explode(',', $result[0]);

				if (empty($result) || (count($result) == 1) && $result[0] == 0) {
					return array(
						'groups_to_remove' => explode(',', $this->_awCpDisablePrice),
						'groups_to_add'    => array()
					);
				}

				if ($result[0] == 0) {
					array_shift($result);
				}

				return array(
					'groups_to_remove' => explode(',', $this->_awCpDisablePrice),
					'groups_to_add'    => $result
				);
			}


			$savedValues = explode(',', $this->_awCpDisablePrice);
		} else {
			if ($scope['cacheTag'] == self::DISABLED_PRODUCT_CACHE_TAG) {


				if ($this->_awCpDisableProductUseDefault) {

					$result = $connection->fetchCol($select);
					$result = explode(',', $result[0]);

					if (empty($result) || (count($result) == 1) && $result[0] == 0) {
						return array(
							'groups_to_remove' => explode(',', $this->_awCpDisableProduct),
							'groups_to_add'    => array()
						);
					}

					if ($result[0] == 0) {
						array_shift($result);
					}

					return array(
						'groups_to_remove' => explode(',', $this->_awCpDisableProduct),
						'groups_to_add'    => $result
					);
				}

				$savedValues = explode(',', $this->_awCpDisableProduct);
			} else {
				return array('groups_to_remove' => array(), 'groups_to_add' => array());
			}
		}

		$reqGroups = $scope['attribute'];


		$groupsToAdd = array_diff($reqGroups, $savedValues);
		$groupsToRemove = array_diff($savedValues, $reqGroups);


		return array('groups_to_remove' => $groupsToRemove, 'groups_to_add' => $groupsToAdd);
	}


}
