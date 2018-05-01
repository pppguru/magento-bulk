<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Components_Customergrid extends Mage_Adminhtml_Block_Widget_Grid_Container
implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
		//Path of the grid
		$this->_controller = 'adminhtml_skubycustomer_components_customergrid';
		
		//Module name
		$this->_blockGroup = 'customreports';		
		
		//$form_block = $this->_blockGroup . '/' . $this->_controller . '_grid';
		
		$this->_headerText = Mage::helper('customreports')->__('Your search results');		
        parent::__construct();
		$this->_removeButton('add');
    }
	
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		return $this->toHtml();
	}
}
