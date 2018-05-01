<?php
/**
 * Rewrites core Mage_Catalog_Model_Product_Type_Price
 * Reason: make getGroupPrice percentage working with tier prices
 * @author   Erik - Sep 21, 2016
 */
class Bulksupplements_Catalog_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{    	
	public function round($price) {
        return round($price, 2);
    }
    
    public function convertPrice($tierPrice, $price) {
        if (strpos($tierPrice, '%') !== false) {
            $tierPrice = $this->round(max(0, $price * ((float)$tierPrice/100)));
        }
        return max(0, $tierPrice);
    }

    /**
     * Get product group price
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getGroupPrice($product)
    {

        $groupPrices = $product->getData('group_price');

        if (is_null($groupPrices)) {
            $attribute = $product->getResource()->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $groupPrices = $product->getData('group_price');
            }
        }

        if (is_null($groupPrices) || !is_array($groupPrices)) {
            return $product->getPrice();
        }

        $customerGroup = $this->_getCustomerGroupId($product);

        $matchedPrice = $product->getPrice();

        $collection = Mage::getModel('ewgppercent/group_price')->getCollection();
        $collection->addFieldToFilter('entity_id', $product->getId());
        
        $data = $collection->getData();
        foreach ($data as &$item) {
            $val = $this->convertPrice($item['value'], $product->getPrice());
            if ($item['customer_group_id'] == $customerGroup && $val < $matchedPrice) {
                $matchedPrice = $val;
            }
        }

        return $matchedPrice;
    }

    /**
     * Get product tier price by qty
     *
     * @param   float $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  float
     */
    public function getTierPrice($qty = null, $product)
    {
        $allGroups = Mage_Customer_Model_Group::CUST_GROUP_ALL;
        $prices = $product->getData('tier_price');

        if (is_null($prices)) {
            $attribute = $product->getResource()->getAttribute('tier_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('tier_price');
            }
        }

        if (is_null($prices) || !is_array($prices)) {
            if (!is_null($qty)) {
                return $product->getPrice();
            }
            return array(array(
                'price'         => $product->getPrice(),
                'website_price' => $product->getPrice(),
                'price_qty'     => 1,
                'cust_group'    => $allGroups,
            ));
        }

        $custGroup = $this->_getCustomerGroupId($product);

        $collection = Mage::getModel('ewgppercent/tier_price')->getCollection()->setOrder('qty', 'ASC');
        $collection->addFieldToFilter('entity_id', $product->getId());
        
        $data = $collection->getData();
        $gp = $this->getGroupPrice($product);

        foreach ($prices as $key => $price) {
            $prices[$key]['price'] = $this->convertPrice($data[$key]['value'], $gp);
            $prices[$key]['website_price'] = $this->convertPrice($data[$key]['value'], $gp);
        }

        if ($qty) {
            $prevQty = 1;
            $prevPrice = $product->getPrice();
            $prevGroup = $allGroups;

            foreach ($prices as $key => $price) {
                if ($price['cust_group']!=$custGroup && $price['cust_group']!=$allGroups) {
                    // tier not for current customer group nor is for all groups
                    continue;
                }
                if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
                    continue;
                }
                if ($price['price_qty'] < $prevQty) {
                    // higher tier qty already found
                    continue;
                }
                if ($price['price_qty'] == $prevQty && $prevGroup != $allGroups && $price['cust_group'] == $allGroups) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    continue;
                }
                if ($price['website_price'] < $prevPrice) {
                    $prevPrice  = $price['website_price'];
                    $prevQty    = $price['price_qty'];
                    $prevGroup  = $price['cust_group'];
                }
            }
            return $prevPrice;
        } else {
            $qtyCache = array();
            foreach ($prices as $i => $price) {
                if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
                    unset($prices[$i]);
                } else if (isset($qtyCache[$price['price_qty']])) {
                    $j = $qtyCache[$price['price_qty']];
                    if ($prices[$j]['website_price'] > $price['website_price']) {
                        unset($prices[$j]);
                        $qtyCache[$price['price_qty']] = $i;
                    } else {
                        unset($prices[$i]);
                    }
                } else {
                    $qtyCache[$price['price_qty']] = $i;
                }
            }
        }

        return ($prices) ? $prices : array();
    }
}
