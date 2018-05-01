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
 * @copyright  Copyright (c) 2013 Boostmyshop (http://www.boostmyshop.com)
 * @author : Guillauem SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_QtyNegative extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{  

    /**
     * Product  with qty is negative
     * @return type
     */
    private static function getNegativeQtys(){
      $tableStock = Mage::helper('HealthyERP')->getPrefixedTableName('cataloginventory_stock_item');
      $tableProducts = Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity');

      $sql='SELECT cisi.product_id as product_id, cpi.sku as sku, cisi.stock_id as stock_id, cisi.qty as qty
        FROM '.$tableStock.' cisi, '.$tableProducts.' cpi
        WHERE cisi.product_id = cpi.entity_id
        AND cisi.qty < 0';

      return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }



    public static function getErrorsList(){
      return self::getNegativeQtys();
    }


    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Product with stock problem').' : '.$this->_countToFix.'<br/>';

        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
              if($this->_countToFix > 0){
                 foreach ($this->_idListToFix as $item) {
                    $situation .= "<p>".$item['sku']." - Warehouse# ".$item['stock_id']." <b>Qty = ".$item['qty']."</b></p>";
                 }
               }
              }
            break;
          default:
             $situation .= $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }
      }
      return $situation;
    }


  /**
   * Create stock movements of update quantity depending of the fix selected
   *
   * @param type $action
   * @return boolean
   */
    public static function fixIssue($action){

       $redirect = true;
       $productListToFix = self::getErrorsList();
       $date = date('Y-m-d H:i');

       foreach ($productListToFix as $item) {
           $productId = $item['product_id'];

           if($productId>0){
              $qtyNegative = $item['qty'];

               if($qtyNegative<0){
                   $qtyForFix= -$qtyNegative;
                   mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                       $productId,
                       null,
                       $item['stock_id'],
                       $qtyForFix,
                       mage::helper('AdvancedStock')->__('Adjustment because Qty was %s', $qtyNegative),
                       array('sm_date' => $date, 'sm_type' => 'adjustment'));
               }
          }
        }

      
      return $redirect;
    }

}