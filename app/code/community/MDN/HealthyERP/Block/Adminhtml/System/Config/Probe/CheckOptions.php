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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_CheckOptions extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{   

    const DEFAULT_ACTION = 'fix';

    const ID_OPTION_KEY = 0;
    const ID_PATH = 1;
    const ID_FIX_VALUE = 2;
    const ID_DESCRIPTION = 3;

    //options : KEY, path, expectedValue
    private static $_optionsTocheck = array(
        array('OPT1','cataloginventory/options/can_subtract', 0, 'Catalog -> Inventory -> Stock option -> Decrease Stock When Order is Placed'),
        array('OPT2','cataloginventory/options/can_back_in_stock', 0, 'Catalog -> Inventory -> Stock option -> Set Items Status to be In Stock When Order is Cancelled'),
        );



    /**
     * Check if each option defined in $_optionsTocheck match with the expected result
     * @return type
     */
    public static function getErrorsList(){

      $errorList = array();

      foreach (self::$_optionsTocheck as $optionSet){
        $optionKey = $optionSet[self::ID_OPTION_KEY];
        $optionPath = $optionSet[self::ID_PATH];
        $optionValueExpected = $optionSet[self::ID_FIX_VALUE];

        //If the option value does not match with the expected result, add it to error list
        if( Mage::getStoreConfig($optionPath) != $optionValueExpected){
          $errorList[] = $optionKey;
        }
      }

      return $errorList;
    }


    protected function getActions()
    {
      $actions = array();

      $label = $this->__('Fix these options');

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
        $situation = $this->__('Options with uncorrect settings').' : '.$this->_countToFix.'<br/>';

        switch($this->_indicator_status){
          case parent::STATUS_OK :
             break;
          case parent::STATUS_NOK :
          case parent::STATUS_PARTIAL :
             if($this->_countToFix>0){
              $situation .= $this->__('Option list').' : <br/>';
              foreach ($this->_idListToFix as $id) {
                 $situation .= $this->__('Options %s with uncorrect settings ',self::getElementFromOptionList($id,self::ID_DESCRIPTION)).'<br/>';
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

      $redirect = true;
      
      $optionListToFix = self::getErrorsList();
      
      if(count($optionListToFix)>0){
         foreach ($optionListToFix as $key => $idToFix) {              
             Mage::getConfig()->saveConfig(
                     self::getElementFromOptionList($idToFix,self::ID_PATH),
                     self::getElementFromOptionList($idToFix,self::ID_FIX_VALUE));
         }
         Mage::getConfig()->cleanCache();
      }      

      return $redirect;
    }

    private static function getElementFromOptionList($id,$index){
          $element = null;
          foreach (self::$_optionsTocheck as $optionSet){
            if($id == $optionSet[self::ID_OPTION_KEY]){
              $element = $optionSet[$index];
              break;
            }
          }
          return $element;
    }

}