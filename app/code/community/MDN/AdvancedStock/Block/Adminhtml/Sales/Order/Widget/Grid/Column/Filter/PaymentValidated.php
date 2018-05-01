<?php

class MDN_AdvancedStock_Block_Adminhtml_Sales_Order_Widget_Grid_Column_Filter_PaymentValidated
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select 
{
	protected function _getOptions()
    {
        $retour = array();
        $retour[] = array('label' => $this->__('All'), 'value' => '');
        $retour[] = array('label' => $this->__('Yes'), 'value' => '1');
        $retour[] = array('label' => $this->__('No'), 'value' => '0');
        return $retour;
    }	
    
    public function getCondition()
    {
    	$searchString = $this->getValue();
    	if ($searchString == '')
    		return;
    		
    	$productIds = array();
    	
    	switch ($searchString)
    	{
    		case '0':
    			$productIds = mage::getModel('sales/order')
    								->getCollection()
    								->addAttributeToFilter('payment_validated', array('neq' => 1))
    								->getAllIds();    								
    			break;
    		case '1':
    			$productIds = mage::getModel('sales/order')
    								->getCollection()
    								->addAttributeToFilter('payment_validated', 1)
    								->getAllIds();
    			break;
    	}
    	
    	return array('in' => $productIds);
    }
}