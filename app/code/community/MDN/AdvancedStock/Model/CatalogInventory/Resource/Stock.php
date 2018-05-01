<?php

/**
 * Overrides this class to fix multiple warehouse issues 
 */
class MDN_AdvancedStock_Model_CatalogInventory_Resource_Stock extends Mage_CatalogInventory_Model_Resource_Stock {

    /**
     * add join to select only in stock products
     *
     * @param Mage_Catalog_Model_Resource_Product_Link_Product_Collection $collection
     * @return Mage_CatalogInventory_Model_Resource_Stock
     */
    public function setInStockFilterToCollection($collection) {
        $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $cond = array(
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1  and {{table}}.stock_id = 1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0 and {{table}}.stock_id = 1'
        );

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1 AND {{table}}.stock_id = 1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.stock_id = 1';
        }

        $collection->joinField(
                'inventory_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '(' . join(') OR (', $cond) . ')'
        );
        return $this;
    }

}
