<?php
abstract class Extendware_EWGPPercent_Model_Override_Mage_Catalog_Product_Attribute_Backend_Groupprice_Abstract extends Extendware_EWGPPercent_Model_Override_Mage_Catalog_Product_Attribute_Backend_Groupprice_Abstract_Bridge
{

	private function isAttributeProcessable() {
		return in_array($this->getAttribute()->getName(), array('tier_price', 'group_price'));
	}
	
	public function afterLoad($object)
    {
    	if ($this->isAttributeProcessable() === false) {
    		return parent::afterLoad($object);
    	}
    	 
    	//$this->loadEWData($object);
        return parent::afterLoad($object);
    }
    
	public function loadEWData($object) {
		if ($this->isAttributeProcessable() === false) {
    		return $this;
    	}

    	$loadedKey = 'ew' . $this->getAttribute()->getName() . '_loaded';
    	if ($object->hasOrigData($loadedKey) === false) {
    		$object->setOrigData($loadedKey, true);

    		$storeId   = $object->getStoreId();
	        $websiteId = null;
	        if ($this->getAttribute()->isScopeGlobal()) {
	            $websiteId = 0;
	        } else if ($storeId) {
	            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
	        }
	
	    	$data = self::_getDynamicResource()->loadPriceData($object->getId(), $websiteId);
			foreach ($data as $k => $v) {
	            $data[$k]['website_price'] = $v['price'];
	            if ($v['all_groups']) {
	                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
	            }
	        }
	
	        $object->setOrigData('ew' . $this->getAttribute()->getName(), $data);
	
	        $valueChangedKey = 'ew' . $this->getAttribute()->getName() . '_changed';
	        $object->setOrigData($valueChangedKey, 0);
	        
	        $currentData = $object->getData('ew' . $this->getAttribute()->getName());
	        if (is_array($currentData) === false) {
		        $object->setData('ew' . $this->getAttribute()->getName(), $data);
		        $object->setData($valueChangedKey, 0);
	        } else {
	        	$object->setData($valueChangedKey, 1);
	        }
    	}

    	return $this;
    }
    
	public function afterSave($object)
    {
    	if ($this->isAttributeProcessable() === false) {
    		return parent::afterSave($object);
    	}
    	
    	$storeId   = $object->getStoreId();
        $websiteId  = Mage::app()->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal   = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;
        
        $this->loadEWData($object);
    	$origDynTiers = self::getOrigDynamicTiers($object);
    	$dynTiers = $object->getData('ew' . $this->getAttribute()->getName());
    	
    	// delete data if the dynamic tiers is empty
    	if (empty($dynTiers)) {
            if ($isGlobal) {
                self::_getDynamicResource()->deletePriceData($object->getId());
            } else {
                self::_getDynamicResource()->deletePriceData($object->getId(), $websiteId);
            }
            $object->setData($this->getAttribute()->getName(), array());
            return parent::afterSave($object);
        }
        
        $origTiers = self::getOrigTiers($object);
    	$tiers = $object->getData($this->getAttribute()->getName());

    	list($insert, $update, $delete, $old) = self::hasDataChangedForPriceRows($isGlobal, $dynTiers, $origDynTiers);

    	$isChanged  = false;
        $productId  = $object->getId();
		
        $attribute = Mage::helper('ewgppercent')->getReferenceAttributeCode();
        if ($object->getData($attribute) != $object->getOrigData($attribute)) {
        	$isChanged = true;
        }

        if (!empty($delete)) {
            foreach ($delete as $data) {
                self::_getDynamicResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                self::_getDynamicResource()->savePriceData($price);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new Varien_Object(array(
                        'value_id'  => $old[$k]['price_id'],
                        'value'     => $v['value']
                    ));
                    self::_getDynamicResource()->savePriceData($price);

                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = 'ew' . $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
            
            // alter tier data based on the dynamic tiers
            $tiers = array();

            foreach ($dynTiers as $tier) {
            	unset($tier['price_id']);
            	unset($tier['website_price']);
            	$tier['price'] = Mage::helper('ewgppercent')->convertPrice($tier['price'], Mage::helper('ewgppercent')->getPriceValueFromObject($object, $tier));
            	$tiers[] = $tier;
            }

            $object->setData($this->getAttribute()->getName(), $tiers);
        }
        
    	return parent::afterSave($object);
    }
    
	protected function _getAdditionalUniqueFields($objectArray)
    {
    	// this is to support old versions of magento that only have tier prices
    	if (is_subclass_of($this, 'Mage_Catalog_Model_Product_Attribute_Backend_Groupprice_Abstract') === false) {
    		return array('qty' => $objectArray['price_qty'] * 1);
    	}
        return parent::_getAdditionalUniqueFields($objectArray);
    }
    
	private function hasDataChangedForPriceRows($isGlobal, array $priceRows, array $origGroupPrices) {
    	// add dynamic tiers to the database
    	$old = array();
        $new = array();

        // prepare original data for compare
        if (!is_array($origGroupPrices)) {
            $origGroupPrices = array();
        }
        foreach ($origGroupPrices as $data) {
            if ($data['website_id'] > 0 || ($data['website_id'] == '0' && $isGlobal)) {
                $key = join('-', array_merge(
                    array($data['website_id'], $data['cust_group']),
                    $this->_getAdditionalUniqueFields($data)
                ));
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key = join('-', array_merge(
                array($data['website_id'], $data['cust_group']),
                $this->_getAdditionalUniqueFields($data)
            ));

            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;

            $new[$key] = array_merge(array(
                'website_id'        => $data['website_id'],
                'all_groups'        => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'value'             => $data['price'],
            ), $this->_getAdditionalUniqueFields($data));
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        return array($insert, $update, $delete, $old);
    }
    
    private function _getDynamicResource() {
    	return Mage::getResourceModel('ewgppercent/' . $this->getAttribute()->getName());
    }
    
	private function getOrigDynamicTiers($object) {
		$this->loadEwData($object);
		return $object->getOrigData('ew' . $this->getAttribute()->getName());
	}
	
	private function getOrigTiers($object) {
		return $object->getOrigData($this->getAttribute()->getName());
	}
}
