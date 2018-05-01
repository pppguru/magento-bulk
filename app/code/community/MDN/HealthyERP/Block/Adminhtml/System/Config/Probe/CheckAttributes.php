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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_CheckAttributes extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

    const ID_ATTRIBUTE_SCOPE = 0;
    const ID_ATTRIBUTE_KEY = 1;
    const ID_ATTRIBUTE_LABEL = 2;

    //options : KEY, path, expectedValue
   //

    private static $_mandatoryAttribute = array(
        
        //magento attribute
        array('catalog_product','name', 'Product name'),
        array('catalog_product','cost', 'Product cost'),
        array('catalog_product','price', 'Product price'),

        //ERP attributes
        array('catalog_product','outofstock_period_from', 'Product outofstock_period_from'),
        array('catalog_product','outofstock_period_enabled', 'Product outofstock_period_enabled'),
        array('catalog_product','manual_supply_need_comments', 'Product manual_supply_need_comments'),
        array('catalog_product','manual_supply_need_date', 'Product manual_supply_need_date'),
        array('catalog_product','manual_supply_need_qty', 'Product manual_supply_need_qty'),
        array('catalog_product','override_subproducts_planning', 'Product override_subproducts_planning'),
        array('catalog_product','purchase_tax_rate', 'Product purchase_tax_rate'),
        array('catalog_product','exclude_from_supply_needs', 'Product exclude_from_supply_needs'),
        array('catalog_product','default_supply_delay', 'Product default_supply_delay')
    );


    /**
     * Check if each option defined in $_optionsTocheck match with the expected result
     * @return type
     */
    public static function getErrorsList(){

      $errorList = array();

      foreach (self::$_mandatoryAttribute as $attributeToCheck){

        //If the option value does not match with the expected result, add it to error list
        if(!self::attributeIsMissing($attributeToCheck)){
          $errorList[$attributeToCheck[self::ID_ATTRIBUTE_KEY]] = $attributeToCheck[self::ID_ATTRIBUTE_LABEL];
        }
      }

      return $errorList;
    }

    private static function attributeIsMissing($attributeToCheck){
        $attributeExists = false;

        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(
            $attributeToCheck[self::ID_ATTRIBUTE_SCOPE],
            $attributeToCheck[self::ID_ATTRIBUTE_KEY]);

        if($attributeModel != null &&  $attributeModel->getId()>0){
            $attributeExists = true;
        }
        return $attributeExists;

    }


    protected function getActions()
    {
      $actions = array();

      $label = $this->__('Fix these Attributes');

      $action = self::DEFAULT_ACTION;
      $openMode = self::OPEN_URL_CURRENT_WINDOWS;

      switch($this->_indicator_status){
        case self::STATUS_OK :
          break;
        case self::STATUS_PARTIAL :
        case self::STATUS_NOK :
           $actions[] = array($label, $action, $openMode);
           break;
      }
      return $actions;
    }
    


    protected function getCurrentSituation()
    {
      $situation = '';
      
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = $this->__('Attribute missing').' : '.$this->_countToFix.'<br/>';

        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if($this->_countToFix>0){
              $situation .= '<br/>';
              foreach ($this->_idListToFix as $id => $label) {
                 $situation .= $this->__('Attribute missing %s ', $id.'('.$label.')').'<br/>';
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
     * Apply the expected change to each option identified as incorrect
     *
     * @param type $optionListToFix
     * @param type $action
     * @return boolean
     */
    public static function fixIssue($action){

      return false;
    }



}