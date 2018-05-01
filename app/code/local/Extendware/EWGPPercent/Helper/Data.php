<?php
class Extendware_EWGPPercent_Helper_Data extends Extendware_EWCore_Helper_Data_Abstract
{
	public function getReferenceAttributeCode() {
		if ($this->getConfig()->getReferencePriceMode() == 'cost') {
			return 'cost';
		}
		elseif ($this->getConfig()->getReferencePriceMode() == 'group_price') {
			return 'group_price';
		}
		return 'price';
	}
	
	public function getPriceValueFromObject($object, $tier = array()) {
		$attribute = $this->getReferenceAttributeCode();
		if ($attribute == 'price') return $object->getPrice();
		elseif ($attribute == 'cost') return $object->getCost();
		elseif ($attribute == 'group_price') {
			if (!empty($tier)) {
				$custGroup = $tier['cust_group'];

				$groupPrices = $object->getData('ewgroup_price');

				foreach ($groupPrices as $key => $value) {
					if ($value['website_id'] == $tier['website_id'] && $value['cust_group'] == $tier['cust_group']) {
						return $value['price'];
					}
				}
			}
			return $object->getPrice();
		}
		return $object->getData($attribute);
	}
	
	public function getDynamicTierPriceDataForProduct($productId, $price, array $websiteIds = array()) {
		$collection = Mage::getModel('ewgppercent/tier_price')->getCollection();
		$collection->addFieldToFilter('entity_id', $productId);
		if (empty($websiteIds) === false) {
			$collection->addFieldToFilter('website_id', array('in' => $websiteIds));
		}

		$data = $collection->getData();
		foreach ($data as &$item) {
			$item['value'] = $this->convertPrice($item['value'], $price);
            unset($item['value_id']);
			unset($item);
		}
		
		return $data;
	}
	
	public function getDynamicGroupPriceDataForProduct($productId, $price, array $websiteIds = array()) {
		$collection = Mage::getModel('ewgppercent/group_price')->getCollection();
		$collection->addFieldToFilter('entity_id', $productId);
		if (empty($websiteIds) === false) {
			$collection->addFieldToFilter('website_id', array('in' => $websiteIds));
		}
		
		$data = $collection->getData();
		foreach ($data as &$item) {
			$item['value'] = $this->convertPrice($item['value'], $price);
            unset($item['value_id']);
			unset($item);
		}
		
		return $data;
	}
	
	public function round($price) {
		return round($price, 2);
	}
	
	public function convertPrice($tierPrice, $price) {
		if (strpos($tierPrice, '%') !== false) {
            if ($this->getConfig()->getPriceMode() == 'percent_of') $tierPrice = $this->round(max(0, $price * ((float)$tierPrice/100)));
            else $tierPrice = $this->round(max(0, $price * ((float)(100 - $tierPrice)/100)));
		}
		return max(0, $tierPrice);
	}
}