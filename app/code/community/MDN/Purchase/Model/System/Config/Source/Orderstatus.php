<?php

class MDN_Purchase_Model_System_Config_Source_Orderstatus extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options)
        {
            $this->getAllOptions();
        	
        }
        return $this->_options;
    }
    
    //todo: charger la liste des status dynamiquement a partir des fichiers config
    public function getAllOptions()
    {
        if (!$this->_options) {
        	$this->_options = array();
        	$this->_options[] = array('value' => Mage_Sales_Model_Order::STATE_HOLDED ,'label' => 'Holded');
        	$this->_options[] = array('value' => Mage_Sales_Model_Order::STATE_NEW ,'label' => 'New');
        	$this->_options[] = array('value' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT ,'label' => 'Pending payment');
        	$this->_options[] = array('value' => 'pending' ,'label' => 'Pending');
        	$this->_options[] = array('value' => 'pending_paypal' ,'label' => 'Pending Paypal');
        	$this->_options[] = array('value' => 'pending_amazon_asp' ,'label' => 'Pending Amazon ASP');
        	$this->_options[] = array('value' => Mage_Sales_Model_Order::STATE_PROCESSING ,'label' => 'Processing');
        }
        return $this->_options;
    }
}