<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer extends Mage_Adminhtml_Block_Widget_Form_Container
{	
	public function __construct()
    {
        $this->_objectId = 'id';
		//main folder inside the block
        $this->_controller = 'adminhtml';
		//module name
        $this->_blockGroup = 'customreports';
		//folder that contains the main form
		$this->_mode = 'skubycustomer';
		
		//$form_block = $this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form';

        parent::__construct();
		
		$this->_removeButton('reset');
		$this->_removeButton('save');
		$this->_removeButton('back');       
    }

    /**
     * Getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {       
        return Mage::helper('customreports')->__('SKU by Customer');        
    }
}