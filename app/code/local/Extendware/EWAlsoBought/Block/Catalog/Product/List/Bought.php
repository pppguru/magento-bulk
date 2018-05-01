<?php
class Extendware_EWAlsoBought_Block_Catalog_Product_List_Bought extends Mage_Catalog_Block_Product_Abstract
{
	protected $blockType = 'bought';
    protected $_mapRenderer = 'msrp_noform';
    protected $_columnCount = 4;
    protected $_items;
    protected $_itemCollection;
    protected $_itemLimits = array();

	protected function _prepareData() {
		$config = Mage::helper('ewalsobought/config');
		if ($config->getIsEnabledByKey($this->blockType) === false) return $this->callDefaultPrepareData();

		$productId = Mage::registry('product')->getId();
		$collection = Mage::helper('ewalsobought')->getProductsAlsoBoughtCollectionForType($this->blockType, $productId);
		
		$mode = $config->getListingModeByKey($this->blockType);
		if ($mode == 'replace') {
			/*if (!$collection->count()) {
				$this->callDefaultPrepareData('ewalsobought');
				$collection = $this->_itemCollection;
			}*/
		} elseif ($mode == 'fallback') {
			$this->callDefaultPrepareData('ewalsobought');
			$defaultCollection = $this->_itemCollection;
			if ($defaultCollection->count() > 0) {
				$collection = $defaultCollection;
			}
		} elseif ($mode == 'existing_first' or ($mode == 'existing_last' and $collection->count() <= $config->getMaximumProductsByKey($this->blockType))) {
			$this->callDefaultPrepareData('ewalsobought');
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
					$newCollection->addItem($defaultCollection->getItemById($defaultId));
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
    
	protected function callDefaultPrepareData() {
		$this->_itemCollection = new Varien_Data_Collection();
		return $this;
	}
    
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    public function getItemCollection()
    {
        return $this->_itemCollection;
    }

    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    public function getRowCount()
    {
        return ceil(count($this->getItemCollection()->getItems())/$this->getColumnCount());
    }

    public function setColumnCount($columns)
    {
        if (intval($columns) > 0) {
            $this->_columnCount = intval($columns);
        }
        return $this;
    }

    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
    }

    public function getIterableItem()
    {
        $item = current($this->_items);
        next($this->_items);
        return $item;
    }

    /**
     * Set how many items we need to show in upsell block
     * Notice: this parametr will be also applied
     *
     * @param string $type
     * @param int $limit
     * @return Mage_Catalog_Block_Product_List_Upsell
     */
    public function setItemLimit($type, $limit)
    {
        if (intval($limit) > 0) {
            $this->_itemLimits[$type] = intval($limit);
        }
        return $this;
    }

    public function getItemLimit($type = '')
    {
        if ($type == '') {
            return $this->_itemLimits;
        }
        if (isset($this->_itemLimits[$type])) {
            return $this->_itemLimits[$type];
        }
        else {
            return 0;
        }
    }
}
