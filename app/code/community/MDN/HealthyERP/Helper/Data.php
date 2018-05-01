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
class MDN_HealthyERP_Helper_Data extends Mage_Core_Helper_Abstract {

   public static $_table_prefix = '';

   public function getPrefixedTableName($tableName){

      if(empty(self::$_table_prefix)){
        $temp = Mage::getConfig()->getTablePrefix();
        if(!empty($temp)){
          self::$_table_prefix = trim($temp);
        }
      }
      if(!empty(self::$_table_prefix) && strlen(self::$_table_prefix)>0){
        $tableName = self::$_table_prefix.$tableName;
      }
      
      return $tableName;
    }
}
