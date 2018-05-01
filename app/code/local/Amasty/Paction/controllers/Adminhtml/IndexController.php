<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function doAction()
    {
        $productIds  = $this->getRequest()->getParam('product');
        $val         = trim($this->getRequest()->getParam('ampaction_value'));        
        $commandType = trim($this->getRequest()->getParam('command'));
        $storeId     = (int)$this->getRequest()->getParam('store', 0);
        
        try {
            $command = Amasty_Paction_Model_Command_Abstract::factory($commandType);
            
            $success = $command->execute($productIds, $storeId, $val);
            if ($success){
                 $this->_getSession()->addSuccess($success);
            }
            
            // show non critical erroes to the user
            foreach ($command->getErrors() as $err){
                 $this->_getSession()->addError($err);
            }            
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error: %s', $e->getMessage()));
        } 
        
        $this->_redirect('adminhtml/catalog_product/index', array('store'=> $storeId));
        return $this;        
    }
}
