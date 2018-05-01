<?php
class Extendware_EWAlsoBought_Block_Override_Mage_Checkout_Cart_Crosssell extends Extendware_EWAlsoBought_Block_Override_Mage_Checkout_Cart_Crosssell_Bridge
{
	protected $blockType = 'checkout_crosssells';
	protected $skipAlsoViewed = false;
	protected $skipAlsoBought = false;
	
	public function getItems() {
		$items = $this->getData('items');
		if (!is_null($items)) return $items; 
        
		$config = Mage::helper('ewalsobought/config');
		if ($config->getIsEnabledByKey($this->blockType) === false) return $this->callDefaultGetItems('ewalsobought');
		if ($this->skipAlsoViewed) return $this->callDefaultGetItems('ewalsobought');
		
		$cartProductIds = $this->_getCartProductIds();   
		if (empty($cartProductIds)) return $this->callDefaultGetItems('ewalsobought');
        
		$productId = (int)$this->_getLastAddedProductId();
        if ($productId <= 0) $productId = current($cartProductIds);

        $generationMode = $config->getGenerationModeByKey($this->blockType);
        $relevantProductIds = $productId;
        if ($generationMode == 'hybrid') {
        	if ($this->_getLastAddedProductId() <= 0) {
        		$relevantProductIds = $cartProductIds;
        	}
        } elseif ($generationMode == 'whole_cart') {
        	$relevantProductIds = $cartProductIds;
        }
        
        $collection = Mage::helper('ewalsobought')->getProductsAlsoBoughtCollectionForType($this->blockType, $relevantProductIds, $cartProductIds);
        $mode = $config->getListingModeByKey($this->blockType);
		if ($mode == 'replace') {
			/*if (!$collection->count()) {
				$collection = $this->getParentItemCollection('ewalsobought');
			}*/
		} elseif ($mode == 'fallback') {
			$defaultCollection = $this->getParentItemCollection('ewalsobought');
			if ($defaultCollection->count() > 0) {
				$collection = $defaultCollection;
			}
		} elseif ($mode == 'existing_first' or ($mode == 'existing_last' and $collection->count() <= $config->getMaximumProductsByKey($this->blockType))) {
			$defaultCollection = $this->getParentItemCollection('ewalsobought');

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
        
        $items = array();
        foreach ($collection as $item) $items[] = $item;
        $this->setData('items', $items);
        return $this->getData('items');
	}
	
	protected function getParentItemCollection($caller) {
		$items = $this->callDefaultGetItems($caller);
		$collection = new Varien_Data_Collection();
		foreach ($items as $item) $collection->addItem($item);
		return $collection;
	}
	
	protected function _getLastAddedProductId()
    {
    	static $productId = null;
    	if (!$productId) $productId = Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
        return $productId;
    }
    
	protected function callDefaultGetItems($caller) {
		if ($caller == 'ewalsoviewed') {
			return Mage_Checkout_Block_Cart_CrosssellOverriddenClass::getItems();
		}
		return parent::getItems();
	}
}
