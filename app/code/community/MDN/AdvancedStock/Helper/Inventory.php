<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Helper_Inventory extends Mage_Core_Helper_Abstract
{

    //MDN_AdvancedStock_Helper_Inventory::TAG_BY_LOCATION
    const TAG_BY_LOCATION = '__LOCATION_DISABLED__';
    
    /**
     *
     * @param type $data
     */
    public function applyStockTakeForProduct($data){

        if($data){
            $simulation = false;

            $productId = $data['pid'] ;
            $inventoryId = $data['inventory_id'];
            $qtyScanned = $data['qty_scanned'];
            $name = $data['name'];
            $qtyInPicture = $data['qty_picture'];
            $warehouseId = $data['warehouse_id'];
            $warehouseName = $data['warehouse_name'];
            $stockMovementLabel = $data['sm_label'];

            $inventory = Mage::getModel('AdvancedStock/Inventory')->load($inventoryId);
            $inventory->applyForProduct($productId, $name, $qtyInPicture, $qtyScanned, $stockMovementLabel, $warehouseId, $warehouseName, $simulation);
        }

    }
}