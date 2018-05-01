<?php
class Extendware_EWPageCache_Helper_Api extends Extendware_EWCore_Helper_Abstract
{
	private static $secondaryCache;
	protected static $ignoreFlushes = false;
	
	public function __construct() {
		self::$secondaryCache = Mage::getSingleton('ewpagecache/cache_secondary');
	}

	public function setIgnoreFlushes($bool) {
		self::$ignoreFlushes = (bool)$bool;
		return $this;
	}
	
	public function flushPagesByGroup(array $groups = array(), array $storeIds = array()) {
		if (empty($storeIds) === true) {
    		$stores = Mage::app()->getStores(false);
    		foreach ($stores as $store) {
    			$storeIds[] = $store->getId();
    		}
    	} elseif (Extendware_EWCore_Model_Module_License::isFeatureEnabled('Extendware_EWPageCache', 'tagging') === false) {
    		Mage::throwException($this->__('You must have the tagging / auto-flush addon in order to select specific stores.'));
    	}

    	if (empty($groups) === true) {
    		$values = Mage::getModel('ewpagecache/adminhtml_data_option_page_group')->toArray();
    		$groups = array_values($values);
    	}
    	
    	foreach ($storeIds as $storeId) {
	    	$pageMaxAges = $this->mHelper('config')->getPageMaxAges();
	    	foreach ($groups as $group) {
	    		if (!isset($pageMaxAges[$group]) or !is_array($pageMaxAges[$group])) {
	    			$pageMaxAges[$group] = array();
	    		}
	    		$pageMaxAges[$group][$storeId] = time();
	    	}
    	}
    	
    	$this->mHelper('config')->setPageMaxAges(serialize($pageMaxAges));
    	Mage::helper('ewpagecache/config')->reload()->saveConfigToFallbackStorage();
    	
    	return $this;
	}
	
	public function getTagsForFlushFromProductIds(array $ids, $type = 'default', $clearCache = false) {
		static $cache = array();
		if ($clearCache === true) $cache = array();
		
		$ids = array_unique($ids);
		sort($ids, SORT_NUMERIC);
		$key = md5(implode(',', $ids));
		if (isset($cache[$key]) === false) {
			if ($this->mHelper('config')->isFlushParentProductsEnabled() === true) {
				$parentIds = $this->getParentProductIdsFromProductIds($ids);
				if (empty($parentIds) === false) {
					$ids = array_merge($ids, $parentIds);
					$ids = array_unique($ids);
				}
			}
			
			$linkedProductIds = $this->getLinkedProductIdsFromProductIds($ids, $this->mHelper('config')->getProductLinkTypesToFlush());
			if (empty($linkedProductIds) === false) {
				$ids = array_merge($ids, $linkedProductIds);
				$ids = array_unique($ids);
			}
			
			$categoryTags = array();
			if ($type == 'inventory') {
				$categoryIds = Mage::helper('ewpagecache/api')->getCategoryIdsFromProductIds($ids);
				$categoryTags = Mage::helper('ewpagecache/api')->getTagsForFlushFromCategoryIds($categoryIds);
			}
			
			$cache[$key] = array_merge($this->getTagsFromProductIds($ids), $categoryTags);
		}
		
		return $cache[$key];
	}
	
	public function getTagsForFlushFromCmsPageIds(array $ids, $type = 'default', $clearCache = false) {
		return $this->getTagsFromCmsPageIds($ids);
	}
	
	public function getTagsForFlushFromCategoryIds(array $ids, $type = 'default', $clearCache = false) {
		static $cache = array();
		if ($clearCache === true) $cache = array();
		
		$ids = array_unique($ids);
		sort($ids, SORT_NUMERIC);
		$key = md5(implode(',', $ids));
		if (isset($cache[$key]) === false) {
			if ($this->mHelper('config')->isFlushParentCategoriesEnabled() === true) {
				$parentCategoryIds = $this->getParentCategoryIdsFromCategoryIds($ids);
				if (empty($parentCategoryIds) === false) {
					$ids = array_merge($ids, $parentCategoryIds);
					$ids = array_unique($ids);
				}
			}
		}
		
		return $this->getTagsFromCategoryIds($ids);
	}
	
	public function getLinkedProductIdsFromProductIds(array $ids = array(), array $typeIds = array()) {
		static $cache = array();
		if (empty($ids)) return array();
		if (empty($typeIds)) return array();
		
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('catalog_read');
		$select = $read->select()->from($resource->getTableName('catalog_product_link'), array('linked_product_id'));
		$select->where('product_id IN(?)', $ids);
		$select->where('link_type_id IN(?)', $typeIds);
		$linkedIds = $read->fetchCol($select);
		return array_unique($linkedIds);
	}
	
	public function getParentProductIdsFromProductIds(array $ids = array()) {
		if (empty($ids)) return array();
		
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('catalog_read');
		$select = $read->select()->from($resource->getTableName('catalog_product_relation'), array('parent_id'));
		$select->where('child_id IN(?)', $ids);
		return $read->fetchCol($select);
	}
	
	public function getParentCategoryIdsFromCategoryIds(array $ids) {
		if (empty($ids)) return array();
		$allParentIds = array();
		$collection = Mage::getModel('catalog/category')->getCollection();
		
		// Custom Code Started
		$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		$checkout_link = Mage::helper('checkout/url')->getCheckoutUrl();
		
		if(strstr($checkout_link,$currentUrl)){
			$collection->addFieldToFilter('main_table.entity_id', $ids);
		}
		else{
			$collection->addFieldToFilter('entity_id', $ids);
		}
		// Custom Code Ended
		
		$paths = $collection->getColumnValues('path');
		foreach ($paths as $path) {
			$parentIds = explode('/', $path);
			array_pop($parentIds);
			$parentIds = array_filter($parentIds);
			$allParentIds = array_merge($allParentIds, $parentIds);
		}
		return array_unique($allParentIds);
	}
	
	public function getCategoryIdsFromProductIds(array $ids) {
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('catalog_read');
		$select = $read->select()->from($resource->getTableName('catalog_category_product'), array('category_id'));
		$select->where('product_id IN(?)', $ids);
		$categoryIds = $read->fetchCol($select);
		return $categoryIds;
	}
	
	public function getTagsFromProductIds(array $ids) {
		$tags = array();
		foreach ($ids as $id) $tags[] = 'p' . $id;
		return $tags;
	}
	
	public function getTagsFromCategoryIds(array $ids) {
		$tags = array();
		foreach ($ids as $id) $tags[] = 'c' . $id;
		return $tags;
	}
	
	public function getTagsFromCmsPageIds(array $ids) {
		$tags = array();
		foreach ($ids as $id) $tags[] = 'cm' . $id;
		return $tags;
	}
	
	public function addProductIdsAsTagsForSave(array $ids = array()) {
		return $this->addTagsForSave($this->getTagsFromProductIds($ids));
	}
	
	public function addCmsPageIdsAsTagsForSave(array $ids = array()) {
		return $this->addTagsForSave($this->getTagsFromCmsPageIds($ids));
	}
	
	public function addCategoryIdsAsTagsForSave(array $ids = array()) {
		return $this->addTagsForSave($this->getTagsFromCategoryIds($ids));
	}
	
	public function addTagsForSave(array $tags) {
		if (empty($tags) === false) {
			$currentTags = self::$secondaryCache->getTagsForSave();
			$tags = array_merge($currentTags, $tags);
			self::$secondaryCache->setTagsForSave($tags);
		}
		return $this;
	}
	
	public function getCacheBackend() {
		return self::$secondaryCache->getCache();
	}
	
	public function flushPagesMatchingAnyTag(array $tags, $realTime = null, $clearCache = false) {
		static $alreadyFlushed = array();
		if (self::$ignoreFlushes === true) return $this;
		if ($clearCache === true) $alreadyFlushed = array();
		$tagsForFlush = array_diff($tags, $alreadyFlushed);
		if (empty($tagsForFlush) === false) {
			$this->cleanCache(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tagsForFlush, $realTime);
			$alreadyFlushed = array_merge($alreadyFlushed, $tagsForFlush);
		}
		return $this;
	}
	
	public function flushPagesMatchingAnyCacheKey(array $keys) {
		if (self::$ignoreFlushes === true) return $this;
		foreach ($keys as $key) {
			Mage::getSingleton('ewpagecache/cache_secondary')->getCache()->remove($key);
		}
		return $this;
	}
	
	public function setCacheLifetimeForSave($seconds) {
		Mage::getSingleton('ewpagecache/cache_secondary')->setCacheLifetime($seconds);
		return $this;
	}
	
	public function setWillBePrimaryCacheLoadable($bool) {
		Mage::getSingleton('ewpagecache/cache_secondary')->setWillBePrimaryCacheLoadable((bool)$bool);
		return $this;
	}
	
	public function cleanCache($mode = Zend_Cache::CLEANING_MODE_ALL, array $tags = array(), $realTime = null, $flushBlockCache = null) {
		static $hasFlushedBlockCache = false;
		if (self::$ignoreFlushes === true) return $this;
		if ($realTime === null) $realTime = ('realtime' == $this->mHelper('config')->getAutoFlushingMode());
		if ($flushBlockCache === null) $flushBlockCache = $this->mHelper('config')->isAutoFlushingBlockCacheEnabled();
		if ($this->mHelper()->isPageCacheEnabledInConfig() === true) {
			if (in_array($mode, array(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, Zend_Cache::CLEANING_MODE_MATCHING_TAG)) === false) {
				$realTime = true;
			}
			
			if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
				if ($this->mHelper('config')->isTaggingEnabled() === false) {
					return $this;
				}
			}
			
			if ($realTime === true) {
				if ($flushBlockCache === true and $hasFlushedBlockCache === false) {
					Mage::app()->getCacheInstance()->cleanType(Mage_Core_Block_Abstract::CACHE_GROUP);
					$hasFlushedBlockCache = true;
				}
				
				if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
					Mage::getModel('ewpagecache/clean_job')->getCollection()->delete();
				}
				
				$cache = self::$secondaryCache->getCache();
				if ($cache) $cache->clean($mode, $tags);
			} else {
				$job = Mage::getModel('ewpagecache/clean_job');
				$job->setMode($mode);
				$job->setTags(implode("\n", $tags));
				$job->save();
			}
		}
		return $this;
	}
	
////////////////////////////////////////////////////////////////////////
// NOT NEEDED AND MAKES NO SENSE BUT KEPT BECAUSE OF THE ATTRIBUTE CODE
////////////////////////////////////////////////////////////////////////
	/*public function getAffectedParentCategoryIds(array $parentIds, $clearCache = false) {
		static $cache = array();
		if ($clearCache === true) $cache = array();
		
		$key = md5(implode(',', $parentIds));
		if (isset($cache[$key])) return $cache[$key];
		
		$cache[$key] = array();
		$parentIds = array_diff($parentIds, array(1));
		if (empty($parentIds)) return $cache[$key];
		$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'is_anchor');
		if (!$attribute->getId()) return $cache[$key];
		
		$categoryEntity = Mage::getModel('eav/entity_type')->loadByCode('catalog_category');
	    if (!$categoryEntity->getId()) return $cache[$key];
	    	
	    $resource = Mage::getSingleton('core/resource');
		$table = $resource->getTableName($categoryEntity->getEntityTable()) . '_' . $attribute->getBackendType();
		$sql = sprintf('select entity_id from %s where store_id = 0 and value = 1 and attribute_id = %d and entity_id IN (%s)', $table, $attribute->getId(), implode(',', $parentIds));

		$read = $resource->getConnection('catalog_read');
		$results = $read->fetchCol($sql);

		$foundAnchor = false;
		foreach ($parentIds as $parentId) {
			if ($foundAnchor === false and in_array($parentId, $results) === true) {
				$foundAnchor = true;
			}
			if ($foundAnchor === true) {
				$cache[$key][] = $parentId;
			}
		}
		
		return $cache[$key];
	}*/
}

