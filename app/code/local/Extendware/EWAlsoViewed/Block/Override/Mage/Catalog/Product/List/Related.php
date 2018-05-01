<?php
class Extendware_EWAlsoViewed_Block_Override_Mage_Catalog_Product_List_Related extends Extendware_EWAlsoViewed_Block_Override_Mage_Catalog_Product_List_Related_Bridge
{
	protected $blockType = 'related';
	protected $skipAlsoViewed = false;
	protected $skipAlsoBought = false;
	
	protected function _prepareData() {
		$config = Mage::helper('ewalsoviewed/config');
		if ($config->getIsEnabledByKey($this->blockType) === false) return $this->callDefaultPrepareData('ewalsoviewed');
		if ($this->skipAlsoViewed) return $this->callDefaultPrepareData('ewalsoviewed');

		$productId = Mage::registry('product')->getId();
		$collection = Mage::helper('ewalsoviewed')->getProductsAlsoViewedCollectionForType($this->blockType, $productId);
		
		$mode = $config->getListingModeByKey($this->blockType);
		if ($mode == 'replace') {
			/*if (!$collection->count()) {
				$this->callDefaultPrepareData('ewalsoviewed');
				$collection = $this->_itemCollection;
			}*/
		} elseif ($mode == 'fallback') {
			$this->callDefaultPrepareData('ewalsoviewed');
			$defaultCollection = $this->_itemCollection;
			if ($defaultCollection->count() > 0) {
				$collection = $defaultCollection;
			}
		} elseif ($mode == 'existing_first' or ($mode == 'existing_last' and $collection->count() <= $config->getMaximumProductsByKey($this->blockType))) {
			$this->callDefaultPrepareData('ewalsoviewed');
			$defaultCollection = $this->_itemCollection;

			$defaultIds = $defaultCollection->getAllIds();
			if ($config->getExistingSortOrderByKey($this->blockType) == 'random') {
				shuffle($defaultIds);
			}
			
			$defaultIds = array_slice($defaultIds, 0, $config->getMaximumExistingProductsByKey($this->blockType));

			$newCollection = new Varien_Data_Collection();
			if ($mode == 'existing_first') {
				foreach ($defaultIds as $defaultId) {
					$item = $defaultCollection->getItemById($defaultId);
					if ($item and $item->getId() > 0) $newCollection->addItem($item);
				}
				
				foreach ($collection as $item) {
					if ($newCollection->count() >= $config->getMaximumProductsByKey($this->blockType)) continue;
					if ($newCollection->getItemById($item->getId())) continue;
					$newCollection->addItem($item);
				}
			} elseif ($mode == 'existing_last') {
				$newCollection = $collection;
				foreach ($defaultIds as $defaultId) {
					$item = $defaultCollection->getItemById($defaultId);
					if (!$item) continue;
					if ($newCollection->count() >= $config->getMaximumProductsByKey($this->blockType)) continue;
					if ($newCollection->getItemById($item->getId())) continue;
					$newCollection->addItem($item);
				}
			}
			
			$collection = $newCollection;
		}
		
	 	foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        
        $this->_itemCollection = $collection;
        return $this;
	}
	
	protected function callDefaultPrepareData($caller) {
		return parent::_prepareData();
	}
}
