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
class MDN_AdvancedStock_Helper_Warehouse extends Mage_Core_Helper_Abstract {

    const _lineReturn = "\r\n";
    const _exportDelim = ";";

    /**
     * return warehouse matching for website and assignment
     *
     * @param unknown_type $websiteId
     * @param unknown_type $assignment
     */
    public function getWarehouseForAssignment($websiteId, $assignment) {
        //todo : put result in cache

        $retour = null;
        $collection = mage::getModel('AdvancedStock/Assignment')
                ->getCollection()
                ->addFieldToFilter('csa_website_id', $websiteId)
                ->addFieldToFilter('csa_assignment', $assignment);
        foreach ($collection as $item) {
            $stockId = $item->getcsa_stock_id();
            $retour = mage::getModel('AdvancedStock/Warehouse')->load($stockId);
        }
        return $retour;
    }

    /**
     * Return warehouses as combo box
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getWarehousesAsCombo($name, $value = '') {
        $retour = '<select  id="' . $name . '" name="' . $name . '">';
        $collection = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach ($collection as $item) {
            if ($value == $item->getId())
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item->getId() . '" ' . $selected . '>' . $item->getstock_name() . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * return warehouse that have preparation assignment
     */
    public function getWarehousesForPreparation() {
        //get preparation warehouse ids
        //todo : optimize using resource models
        $collection = mage::getModel('AdvancedStock/Assignment')
                ->getCollection()
                ->addFieldToFilter('csa_assignment', MDN_AdvancedStock_Model_Assignment::_assignmentOrderPreparation);
        $ids = array();
        foreach ($collection as $item)
            $ids[] = $item->getcsa_stock_id();

        return mage::getModel('AdvancedStock/Warehouse')
                        ->getCollection()
                        ->addFieldToFilter('stock_id', array('in' => $ids));
    }

    /**
     * Return CSV file with stock levels for warehouse at specific date
     *
     * @param unknown_type $warehouseId
     * @param unknown_type $date
     */
    public function getStockAtDateContent($warehouseId, $date) {

        //headers
        $content = 'manufacturer'.self::_exportDelim.'id'.self::_exportDelim.'sku'.self::_exportDelim.'name'.self::_exportDelim;
        $content .= 'cost'.self::_exportDelim.'stock'.self::_exportDelim.'status'.self::_exportDelim.'reserved'.self::_exportDelim.'ordered';
        $content .= self::_lineReturn;

        if(!$warehouseId)
            return $content;

        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId);

        //get products collection      
        $collection = mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('status')                
                ->addAttributeToSelect('cost')
                ->addFieldToFilter('type_id', 'simple')
                ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=' . $warehouseId, 'left');

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
          $collection = $collection->addAttributeToSelect($manufacturerCode);
        }

        foreach ($collection as $product) {

            //define stock level
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($product->getId(), $warehouseId);
            if (!$stockItem)
                continue;
            
            if ($date != date('Y-m-d'))
                $stockLevel = $stockItem->getQtyFromStockMovement($date);
            else
                $stockLevel = $stockItem->getQty();
            
            if ($stockLevel == 0)
                continue;

            //define price
            $cost = mage::helper('AdvancedStock/Product_Cost')->getProductCostAtDate($product, $date, $stockLevel, $warehouse);

            $manufacturerName = '';
            if($manufacturerCode){
              $manufacturerName = $product->getAttributeText($manufacturerCode);
            }

            //product Status
            $status = ($product->getStatus() == 2)?'disabled':'enabled';
            
            $sku = $this->prepareFieldForCSVExport($product->getSku());
            $name = $this->prepareFieldForCSVExport($product->getName());
            $manufacturerName = $this->prepareFieldForCSVExport($manufacturerName);

            //append line
            $content .= $manufacturerName .self::_exportDelim. $product->getId() . self::_exportDelim.$sku.self::_exportDelim;
            $content .= $name.self::_exportDelim.$cost .self::_exportDelim.$stockLevel .self::_exportDelim. $status.self::_exportDelim;
            $content .= $stockItem->getstock_reserved_qty().self::_exportDelim.$stockItem->getstock_ordered_qty();
            $content .= self::_lineReturn;
        }

        return $content;
    }

    private function prepareFieldForCSVExport($value){

        $value = str_replace(self::_exportDelim, '', $value);
        $value = str_replace(self::_lineReturn, '', $value);

        $value = str_replace("\n", ' ', $value);
        $value = str_replace("\r", ' ', $value);
        $value = str_replace("\t", ' ', $value);

        return trim($value);
    }

    /**
     * Return warehouses as array
     */
    public function getWarehouses($addEmpty = false) {
        $retour = array();
        if ($addEmpty)
            $retour[] = '';
        $collection = mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach ($collection as $item) {
            $retour[$item->getId()] = $item->getstock_name();
        }
        return $retour;
    }

}