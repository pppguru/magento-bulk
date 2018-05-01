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
class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract {


    public function getCondition() {       
        
        $productIds = array();

        $filterApplied = false;

        $value = $this->getValue();
       
        $warehouseId = 0;
        $minStockQty = -1;
        $maxStockQty = -1;

        //var_dump($value);

        if($value && is_array($value)){
          if(array_key_exists('warehouse', $value))
            $warehouseId = $value['warehouse'];

          if(array_key_exists('from', $value))
            $minStockQty = $value['from'];

          if(array_key_exists('to', $value))
            $maxStockQty = $value['to'];
        }

        //Check the default stock management to avoid error
        if(Mage::getStoreConfig('cataloginventory/item_options/manage_stock')){

          //if there is at least 1 filter selected
          if($minStockQty > -1 || $maxStockQty > -1 || $warehouseId > 0){

            //Select the stock managed products depending of the filter values
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'SELECT tbl_stock_item.product_id ';
            $sql .= 'FROM '.$prefix.'cataloginventory_stock_item tbl_stock_item ';
            $sql .= 'LEFT JOIN '.$prefix.'catalog_product_entity tbl_product ';
            $sql .= 'ON tbl_stock_item.product_id = tbl_product.entity_id ';
            $sql .= 'WHERE (tbl_stock_item.use_config_manage_stock = 1 ';
            $sql .= 'OR tbl_stock_item.manage_stock = 1) ';



            if($minStockQty > -1){
              $sql .= ' AND tbl_stock_item.qty >= '.$minStockQty;
            }
            if($maxStockQty > -1){
              $sql .= ' AND tbl_stock_item.qty <= '.$maxStockQty;
            }
            if($warehouseId > 0){
              $sql .= ' AND tbl_stock_item.stock_id = '.$warehouseId;
            }

            $productIds = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);

            //enable to display no result if it's the case
            $filterApplied = true;
          }
        }
        
        if (count($productIds) > 0 || $filterApplied)
          return array('in' => $productIds);
        else
          return null;
    }

    /**
     * Display the filter html including
     *  - the warehouse filter
     *  - the from filter
     *  - the to filter
     * @return string
     */
    public function getHtml() {
      
      $html = '';     

      //warehouse list
      $html .= $this->getWarehouseList($this->getEscapedValue('warehouse'), $this->_getHtmlName().'[warehouse]', $this->_getHtmlId().'[warehouse]');

      $html .= '<div class="range">';
      //from      
      $html .= '<div class="range-line"><span class="label">' . Mage::helper('adminhtml')->__('From').':</span>';
      $html .= '<input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$this->_getHtmlId().'_from" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/></div>';

      //to
      $html .= '<div class="range-line"><span class="label">' . Mage::helper('adminhtml')->__('To').' : </span>';
      $html .= '<input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$this->_getHtmlId().'_to" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/></div>';

      $html .= '</div>';

      
      return $html;
    }

    /**
     * Allow the values defined in the custom HTML filter
     *
     * @param type $index
     * @return null
     */
    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0) || (isset($value['to']) && strlen($value['to']) > 0) || ((isset($value['warehouse']) && strlen($value['warehouse']) > 0))) {
            return $value;
        }
        return null;
    }

    /**
     * Custom warehouse list with the ability to select an option for all warehouse
     *
     * @param type $id
     * @param type $class
     */
    public function getWarehouseList($value,$name,$id/*,$class*/){
        $html = '<select  id="' . $id . '" name="' . $name . '">'; //'.$class.'
        $collection = Mage::getModel('AdvancedStock/Warehouse')->getVisibleWarehouses();

        $selected = '';
        if ($value == 0){
           $selected = ' selected ';
        }
        $html .= '<option value="0"  ' . $selected . '>' . Mage::helper('AdvancedStock')->__('All warehouses') . '</option>';

        foreach ($collection as $item) {
            if ($value == $item->getId())
                $selected = ' selected ';
            else
                $selected = '';
            $html .= '<option value="' . $item->getId() . '" ' . $selected . '>' . $item->getstock_name() . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

}