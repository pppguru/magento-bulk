<?php

class MDN_AdvancedStock_Block_Warehouse_Widget_Grid_Column_Renderer_StockAssignments
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	
    	$retour = '';
    	$assignments = $row->getAssignments();
    	foreach ($assignments as $assignment)
    	{
    		if ($assignment->getcsa_assignment() != MDN_AdvancedStock_Model_Assignment::_assignmentNone)
    		{
	    		$website = mage::getModel('core/website')->load($assignment->getcsa_website_id());
	    		$retour .=  $website->getName().' : '.$this->__($assignment->getcsa_assignment()).'<br>';
    		}
    	}
    	return $retour;
    }
    
}