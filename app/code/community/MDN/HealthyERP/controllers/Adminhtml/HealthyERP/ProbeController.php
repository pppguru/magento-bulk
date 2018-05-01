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
class MDN_HealthyERP_Adminhtml_HealthyERP_ProbeController extends Mage_Adminhtml_Controller_Action {

  /**
   * Router to launch the fix relative to the calling probe
   * Will call the function fixIssue in the class
   */
  public function FixAction(){
      $probeClassName = $this->getRequest()->getParam('type');
      $param = $this->getRequest()->getParam('action');

      if(mage::helper('HealthyERP/Probe')->callProbeAction($probeClassName,'fixIssue', $param)){
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'healthyerp'));
      }
  }

  protected function _isAllowed()
  {
      return Mage::getSingleton('admin/session')->isAllowed('admin/erp/healthyerp');
  }
}