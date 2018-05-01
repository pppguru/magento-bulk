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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_MissingProductAvailibilityStatus extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{
    const DEFAULT_ACTION = 'refresh';

    /**
     * Get all products id not identified with a product availibilty status
     *
     * @return type
     */
    public static function getErrorsList(){
      $tableCpi = Mage::helper('HealthyERP')->getPrefixedTableName('catalog_product_entity');
      $tablePa = Mage::helper('HealthyERP')->getPrefixedTableName('product_availability');

      $sql='SELECT entity_id, sku
            FROM '.$tableCpi.'
            WHERE entity_id NOT IN(
            SELECT pa_product_id 
            FROM '.$tablePa.')
            ORDER BY entity_id;';

      return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }



    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Product without Availibility Status').' : '.$this->_countToFix.'<br/>';

         switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
              if($this->_countToFix>0){
                 $situation .= $this->__('Product list').' : <br/>';
                 foreach ($this->_idListToFix as $diffItem) {
                    $situation .= "Product #". $diffItem['sku']."<br/>";
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
     *
     * Refresh (and create product availibility status for all products with the error
     * The button will call a new windows with group task
     * 
     * @param type $productListToFix
     * @param type $action
     * @return boolean
     */
    public static function fixIssue($action){

      $redirect = true;

      $productListToFix = self::getErrorsList();
      
      if(count($productListToFix)>0){
         $helper = mage::helper('BackgroundTask');

         $taskGroupCode = 'refresh_product_availability_status';
         $helper->AddGroup($taskGroupCode,
                           mage::helper('HealthyERP')->__('Create missing Product Availability Status'),
                           'adminhtml/system_config/edit/section/healthyerp');

         $priority = 5;

         foreach ($productListToFix as $diffItem) {
             $productId = $diffItem['entity_id'];
             if($productId>0){
               $helper->AddTask('Create Product Availability Status for product#'.$productId,
                              'SalesOrderPlanning/ProductAvailabilityStatus',
                              'RefreshForOneProduct',
                              $productId,
                              $taskGroupCode,
                              false,
                              $priority);
            }
         }

       $helper->ExecuteTaskGroup($taskGroupCode);
       $redirect = false;
      }

      return $redirect;
    }

}