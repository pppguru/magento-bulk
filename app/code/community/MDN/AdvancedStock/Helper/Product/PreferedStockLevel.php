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
class MDN_AdvancedStock_Helper_Product_PreferedStockLevel extends Mage_Core_Helper_Abstract {


    public function updateForAllProducts()
    {
        $productIds = mage::helper('AdvancedStock/Product_Base')->getProductIds();
        foreach($productIds as $productId)
        {
            $this->updateForProduct($productId);
        }
        return count($productIds);
    }

    /**
     * return sum of ideal stock for all warehouses
     * @param <type> $productId
     */
    public function getIdealStockLevelForAllStocks($productId)
    {
        $value = 0;
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
        foreach ($stocks as $stock) {
            if ($stock->getstock_disable_supply_needs() != 1)
                $value += $stock->getIdealStockLevel();
        }
        return $value;
    }

    public function updateForProduct($productId, $warehouseId = null) {

        if(!$warehouseId){

            $allowedWarehouses  = $this->getAllowedWarehouseList();
            foreach ($allowedWarehouses as $warehouseIdToRefresh){
                $this->updateForProductByWarehouse($productId, $warehouseIdToRefresh);
            }

        }else{
            $this->updateForProductByWarehouse($productId, $warehouseId);
        }
    }

    /**
     * Calculate prefered stock level for product
     */
    public function updateForProductByWarehouse($productId, $warehouseId) {

        //get suggestion
        $data = $this->getSuggestion($productId,$warehouseId);

        //Minimum Ideal Stock level
        $minimumLevel = mage::getStoreConfig('advancedstock/prefered_stock_level/minimum_levels_to_apply');

        if (!$minimumLevel)
            $minimumLevel = 0;

        if ($data['warning_stock_level'] < $minimumLevel)
            $data['warning_stock_level'] = $minimumLevel;

        if ($data['ideal_stock_level'] < $minimumLevel)
            $data['ideal_stock_level'] = $minimumLevel;

        //update warehouses
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
        foreach ($stocks as $stock) {

            if($stock->geterp_exclude_automatic_warning_stock_level_update()){
                continue;
            }


            if ($this->canModifyWarehouse($stock->getstock_id())) {

                if($warehouseId == $stock->getstock_id()) {

                    //update warning level
                    $needUpdateWarningStockLevel = ($stock->getWarningStockLevel() != $data['warning_stock_level']);

                    //update ideal stock
                    $needUpdateIdealStock = ($stock->getIdealStockLevel() != $data['ideal_stock_level']);

                    //save
                    if ($needUpdateIdealStock || $needUpdateWarningStockLevel) {
                        $this->fastUpdateSuggestions($productId, $stock->getstock_id(),
                            $needUpdateIdealStock, $data['ideal_stock_level'],
                            $needUpdateWarningStockLevel, $data['warning_stock_level']);
                    }
                }
            }
        }
    }

    private function fastUpdateSuggestions($productId, $warehouseId, $needUpdateIdealStock, $idealStockLevel,$needUpdateWarningStockLevel, $warningStockLevel)
    {

        $sql = 'UPDATE ' . Mage::getConfig()->getTablePrefix() . 'cataloginventory_stock_item SET ';

        if ($needUpdateIdealStock)
            $sql .= 'ideal_stock_level = ' . $idealStockLevel . ',  use_config_ideal_stock_level = 0 ';

        if ($needUpdateWarningStockLevel) {

            if ($needUpdateIdealStock)
                $sql .= ', ';

            $sql .= 'notify_stock_qty = ' . $warningStockLevel . ',  use_config_notify_stock_qty = 0 ';
        }

        $sql .= ' WHERE product_id = ' . $productId;

        if ($warehouseId)
            $sql .= ' AND stock_id = ' . $warehouseId;


        mage::log($sql,null,'erp_sales_history.log');

        $this->getConnection()->query($sql);
    }

    private function getConnection(){
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }


    /**
     * Return suggestion based on sales history
     */
    public function getSuggestion($productId, $warehouseId) {

        $data = array('warning_stock_level' => null, 'ideal_stock_level' => null);

        //calculate value
        $createIfNotExist = false;
        $salesHistory = mage::helper('AdvancedStock/Sales_History')->getForOneProduct($productId, $warehouseId, $createIfNotExist);
		
        if (!$salesHistory->getId())
            return $data;

        $formula = mage::getStoreConfig('advancedstock/prefered_stock_level/formula');
        $forecastWeeks = mage::getStoreConfig('advancedstock/prefered_stock_level/calculation_weeks');
        $formula = str_replace('duration', $forecastWeeks, $formula);

        foreach (mage::helper('AdvancedStock/Sales_History')->getRanges() as $index => $range) {
            //replace code for the number of sales
            $formula = str_replace('s' . ($index + 1), $salesHistory->getData('sh_period_' . ($index + 1)), $formula);

            //replace code for the number of weeks
            $formula = str_replace('w' . ($index + 1), $range, $formula);
        }

        //calculate
        $value = eval('return ' . $formula . ';');

        //affect values
        $data['ideal_stock_level'] = $value;
        $percent = Mage::getStoreConfig('advancedstock/prefered_stock_level/substract_percent_to_calculate_warning_stock_level');
        $data['warning_stock_level'] = (int)($value - ($value * $percent / 100)) ;

        return $data;
    }

    /**
     * Return true if we can change preferd stock level for warehouse
     */
    public function canModifyWarehouse($stock_id) {
        $t = $this->getAllowedWarehouseList();
        return in_array($stock_id, $t);
    }

    public function getAllowedWarehouseList() {
        $warehouses = mage::getStoreConfig('advancedstock/prefered_stock_level/enable_for_warehouses');
        return explode(',', $warehouses);
    }

}