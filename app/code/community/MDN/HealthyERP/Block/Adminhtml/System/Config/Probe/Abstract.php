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
class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    //values possible for $_indicator_status
    const STATUS_NOK = 'nok';
    const STATUS_OK = 'ok';
    const STATUS_PARTIAL = 'partial';
    const STATUS_UNKNOWN = 'unknown';

    const SMILEY_EXT = '.png';

    //windows execution mode of the buttons
    const OPEN_URL_NEW_WINDOWS = 'new_windows';
    const OPEN_URL_CURRENT_WINDOWS = 'self_windows';

    const FIX_METHOD = 'fix_method';
    const DEFAULT_ACTION = 'fix';

    const DEFAULT_STATUS_MESSAGE = 'Not checked yet';
    const NO_TARGET_ID = 0;
    const LIST_NON_APPLICABLE_BY_URL = true;

    //Probe Results
    protected $_indicator_status;
    protected $_indicator_current_situation;
    protected $_indicator_actions_available;

    protected $_idListToFix;
    protected $_countToFix;


    
    /**
     * Apply a fix for a current action
     * 
     * Public because called by the MDN_HealthyERP_ProbeController
     *
     * @param array $action
     * @throws Exception
     */
    public static function fixIssue($action){

    }

    /**
     * public call of the errors check it self
     *
     * Public because called by the MDN_HealthyERP_ProbeController
     *
     * @param array $action
     * @throws Exception
     */
    public static function getErrorsList(){

    }

    /**
     * This action will be processed at begin and will return the status and the check results
     */
    public function checkProbe() {
        $this->_idListToFix = static::getErrorsList();
        $this->_countToFix = count($this->_idListToFix);
        return static::getErrorStatus($this->_countToFix);
    }

    /**
     * public call of the errors status
     *
     * Public because called by the MDN_HealthyERP_ProbeController
     *
     * @param int $count
     * @throws Exception
     */
    public static function getErrorStatus($count){
        return ($count==0)?self::STATUS_OK:self::STATUS_NOK;
    }
    

    /**
     * Return the current status of this probe
     * @throws Exception
     */
    protected function getCurrentSituation(){
      throw new Exception ('getCurrentSituation to implement');
    }

    /*
     * Returns an array of actions to define the buttons for the Probe
     */
    protected function getActions()
    {
      $actions = array();

      $label = $this->__('Fix them all');
      
      $action = self::DEFAULT_ACTION;
      $openMode = self::OPEN_URL_NEW_WINDOWS;

      switch($this->_indicator_status){
        case self::STATUS_OK :
          break;
        case self::STATUS_PARTIAL :
        case self::STATUS_NOK :
            //display a fix button only if there is a problem
           $actions[] = array($label, $action, $openMode);
           break;
      }
      return $actions;
    }

   

    //------------------------------------------------------------------------------------
    //DISPLAY

    /**
     * ENTRY POINT
     *
     * This will display
     *  - a description of the probe
     *  - the current result of the check done by the probe
     *  - the actions buttons if a problem if detected. they enable end user to fix the issues
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return type
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
      $this->getIndicators();
      return $this->displayProbeResult();
    }

    private function getIndicators()
    {
        $this->_indicator_status = $this->checkProbe();
        $this->_indicator_current_situation = $this->getCurrentSituation();
        $this->_indicator_actions_available = $this->getActions();
    }

    private function displayProbeResult()
    {
        return $this->getCssModifier().$this->displayCurrentSituation().$this->displayActionsButtons();
    }



    
    /**
     * Modify the width of the "System Configuration" columns to display larger information inside
     *
     * @return string
     */
    protected function getCssModifier()
    {
      $html = '<style>
        .columns .form-list {width: 1000px !important;}
        .columns .form-list td.label {width: 100px !important;}
        .columns .form-list td.value {width: 800px !important;}
        .columns .form-list td.scope-label {width: 100px !important;}
        </style>';
      
      return $html;
    }

    /**
     * Display an icon status for this probe (OK/NOK/Partially OK)
     * depending of the current _indicator_status
     *
     * @return String  : image name to display
     */
    protected function getSmiley()
    {      
      $smileyName = '';

      if($this->_indicator_status){       
        switch($this->_indicator_status){
          case self::STATUS_OK :
             $smileyName = 'success-msg'; //green icon
             break;
          case self::STATUS_PARTIAL :
          case self::STATUS_NOK :
             $smileyName = 'error-msg'; //red icon
             break;          
          default:
             $smileyName = 'i_question-mark';//blue icon
             break;
        }
        $this->_indicator_status;
      }
      
      return $smileyName.self::SMILEY_EXT;
    }
   
    /**
     * Display the state of the probe
     *
     * @return String the state of the probe in HTML
     */
    protected function displayCurrentSituation()
    {
      $situation = $this->__('Not checked yet');

      if($this->_indicator_current_situation){
        $situation = $this->_indicator_current_situation;
      }
      switch($this->_indicator_status){
        case self::STATUS_OK :
           $color = 'green';
           break;
        case self::STATUS_NOK :
           $color = 'red';
           break;
        case self::STATUS_PARTIAL :
           $color = 'orange';
           break;
        default:
           $color = 'black';
           break;
      }      
      $html = '<p><img src="'.$this->getSkinUrl('images/'.$this->getSmiley()).'">';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $html .= '<font color="'.$color.'">'.$situation.'</font>';
      }
      $html .= '</p>';
      return $html;
    }

    /**
     * Give the URL to run the action deepding of the result of the errors
     *
     * @param type $targetId
     * @param type $action
     */
    protected function getActionUrl($action){
      
      $url = Mage::helper('adminhtml')->getUrl('adminhtml/HealthyERP_Probe/Fix', array(
             'type' => get_class($this),
             'action' => $action));

      return $url;
    }

    /**
     * Display the button that enable the end user to fix the problems detected by the probe
     *
     * @return String the buttons of the probe in HTML
     */
    protected function displayActionsButtons()
    {
      $html = '';

      if(!empty($this->_indicator_actions_available)){
        foreach ($this->_indicator_actions_available as $actionArray) {
          $label = $actionArray[0];
          $action = $actionArray[1];
          $openMode = $actionArray[2];

          //Get the action URL from the probe
          $url = $this->getActionUrl($action);

          //open the action on the same windows
          $js = "setLocation('$url')";

          //enable to open the action on new windows if the option is set
          if($openMode == self::OPEN_URL_NEW_WINDOWS){
            $js = "window.open('$url','_blank');";
          }

          $html.= '<br/><button type="button" onclick="'.$js.'" style=""><span> - '.$label.' - </span></button><br/>';
        }
      }
      
      return $html;
    }
    
    
}