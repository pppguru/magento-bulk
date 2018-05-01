<?php

class MDN_AdvancedStock_Model_CatalogInventory_Mysql4_Stock_Item extends Mage_CatalogInventory_Model_Mysql4_Stock_Item
{

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Entity_Product_Collection $productCollection
     * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item
     */
    public function addCatalogInventoryToProductCollection($productCollection)
    {
        $isStockManagedInConfig = (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $inventoryTable = $this->getTable('cataloginventory/stock_item');
        $productCollection->joinTable('cataloginventory/stock_item',
            'product_id=entity_id',
            array(
                'is_saleable' => new Zend_Db_Expr(
                    "(
                        IF(
                            IF(
                                $inventoryTable.use_config_manage_stock,
                                 $isStockManagedInConfig,
                                $inventoryTable.manage_stock
                            ),
                            $inventoryTable.is_in_stock,
                            1
                        )
                     )"
            ),
                'inventory_in_stock' => 'is_in_stock'
            ),
            'stock_id = 1', 'left');		//CHANGE MDN
        return $this;
    }

}
