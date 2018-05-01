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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Backgroundtasks extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

    //Number of perding background tasks
    const MID_ALERT = 500;
    const HIGH_ALERT = 3000;

    const DEFAULT_ACTION = 'cron';

    private $_nbtaskpendings = 0;
    private $_lasttasksucessfull = null;

    public static function getNbPendingbackGroundTask(){
        $table = Mage::helper('HealthyERP')->getPrefixedTableName('backgroundtask');

        //get the number of pending background tasks
        $sql = 'SELECT COUNT( * )
			  FROM  '.$table.'
			  WHERE  bt_result IS NULL
			  AND bt_group_code IS NULL;';

        return (int)mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
    }

    public static function getLastSuccessFullTask(){

        $table = Mage::helper('HealthyERP')->getPrefixedTableName('backgroundtask');

        //get the last task sucessfully executed
        $sql = "SELECT bt_id, bt_created_at, bt_executed_at, bt_description, bt_duration, bt_priority
              FROM ".$table."
              WHERE bt_result = 'success'
              ORDER BY bt_executed_at DESC
              LIMIT 1";

        return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }


    public static function getErrorsList(){
        return self::getNbPendingbackGroundTask();
    }
    /**
     * Count and save the number of pending background tasks
     *
     * Store the last task sucessfully executed
     *
     * @return type
     */
    public function checkProbe()
    {
      $this->_nbtaskpendings = self::getNbPendingbackGroundTask();
      $this->_lasttasksucessfull = self::getLastSuccessFullTask();
      return $this->getErrorStatus($this->_nbtaskpendings);
    }

    public static function getErrorStatus($count){
        if($count < self::MID_ALERT){
            $status = parent::STATUS_OK;
        }else if($count > self::HIGH_ALERT){
            $status = parent::STATUS_NOK;
        }else{
            $status = parent::STATUS_PARTIAL;
        }
        return $status;
    }
    


    protected function getActions()
    {
      $actions = array();
      
      $label = $this->__('Launch background tasks processing');
      $action = self::DEFAULT_ACTION;
      $openMode = parent::OPEN_URL_NEW_WINDOWS;

      switch($this->_indicator_status){
        case parent::STATUS_OK :          
        case parent::STATUS_PARTIAL :
        case parent::STATUS_NOK :           
           $actions[] = array($label, $action, $openMode);
           break;
       
      }
      return $actions;
    }    

    protected function getCurrentSituation()
    {
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = '<b>'.$this->__('Pending backgrounds tasks').' : '.$this->_nbtaskpendings.'</b><br/>';

        if (Mage::getStoreConfig('healthyerp/options/display_advanced_message')){
          if(count($this->_lasttasksucessfull)>0 && $this->_lasttasksucessfull[0]){
            $situation .= '<i>'.$this->__('Last successful task is').' : <br/>'.$this->_lasttasksucessfull[0]['bt_description'].' at '.$this->_lasttasksucessfull[0]['bt_executed_at'];
            $situation .= ' '.$this->__('created at').' '.$this->_lasttasksucessfull[0]['bt_created_at'].'<br/>';
            $situation .= ' '.$this->__('during').' '.$this->_lasttasksucessfull[0]['bt_duration'].'ms';
            $situation .= ' '.$this->__('with a priority of ').' '.$this->_lasttasksucessfull[0]['bt_priority'].'</i><br/>';
          }else{
            $situation .= '<b><font color="red">'.$this->__('NO Last Successfull task !').'</font></b><br/>';
          }
        }
        switch($this->_indicator_status){
          case parent::STATUS_OK :
             $situation .= $this->__('The number of background task is acceptable');
             break;
          case parent::STATUS_NOK :
             $situation .= $this->__('The number of background task is too high, please launch the cron manually to perform some checks or set up the cron to run faster');
             break;
          case parent::STATUS_PARTIAL :
             $situation .= $this->__('The number of background task begin to be high, please check this regulary and please launch the cron manually to perform some checks');
             break;
          default:
             $situation .= $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }
      }

      return $situation;
    }

    public static function fixIssue($action){

      mage::helper('BackgroundTask')->ExecuteTasks();

      return true;
    }

}