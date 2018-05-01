<?php

class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Views extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

    const COUNT_VIEW = 4;

    const DEFAULT_ACTION = 'fix';
    const ACTION_MASS_FIX = 'ALL';


    public static function getErrorsList(){
        $missing_views = array();
        $conn = mage::getResourceModel('sales/order_item_collection')->getConnection();

        //View list
        $viewList = Mage::helper('HealthyERP/View')->getViewList();
        foreach ($viewList as $view) {

            //Check if a View (or a Table) with this name exists
            try {
                $sql = 'DESC '. $view;
                $result = $conn->fetchOne($sql);
            }catch (Exception $ex){
                $result = false;
            }

            //Check if it's really a view
            /*try {
                if($result){
                    $sql = "select count(*) from INFORMATION_SCHEMA.VIEWS where table_name = '$view' ";
                    $viewCount = (int)$conn->fetchOne($sql);
                    if($viewCount==1){
                        $result = true;
                    }
                }
            }catch (Exception $ex){
                mage::logException($ex->getMessage().' '.$ex->getTraceAsString());
            }*/

            if(!$result){
                $missing_views[] = $view;
            }
        }
        return $missing_views;
    }



    public static function getErrorStatus($count){

        $status = parent::STATUS_OK;

        if($count>0){
            $status = parent::STATUS_PARTIAL;
        }
        if($count==self::COUNT_VIEW){
            $status = parent::STATUS_NOK;
        }
        return $status;
    }

    protected function getActions()
    {
      $actions = array();

      $action = self::DEFAULT_ACTION;
      $openMode = null;

      switch($this->_indicator_status){
        case parent::STATUS_OK :
          break;
        case parent::STATUS_PARTIAL :
        case parent::STATUS_NOK :
           foreach($this->_idListToFix as $viewTofix){
             $label = $this->__('CREATE '.$viewTofix);
             $actions[] = array($label, $viewTofix, $action, $openMode);
           }
           break;
      }
      $actions[] = array($this->__('TRY TO RECREATE ALL VIEWS'), self::ACTION_MASS_FIX, $action, $openMode);
      return $actions;
    }    

    protected function getCurrentSituation()
    { 
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        switch($this->_indicator_status){
          case parent::STATUS_OK :
             $situation = $this->__('All views are present');
             break;
          case parent::STATUS_NOK :
             $situation = $this->__('None of the view are present');
             break;
          case parent::STATUS_PARTIAL :
             $missingList = implode(", ", $this->_idListToFix);
             $situation = $this->__('Some views are missing : %s',$missingList);
             break;
          default:
             $situation = $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }      
      }
      return $situation;
    }


    /**
     * Re create the missing view
     */
    public static function fixIssue($view){

        $redirect = true;

        if($view == self::ACTION_MASS_FIX){

            Mage::helper('HealthyERP/View')->DropAndRecreateSupplyNeedsViews();

        }else{
            //Get the SQL to recreate the view
            $sql = Mage::helper('HealthyERP/View')->getCreateViewQuery($view);

            //Execute the SQL
            if(!empty($sql)){
                mage::getResourceModel('sales/order_item_collection')->getConnection()->query($sql);
            }
        }
      
        return $redirect;
    }



}