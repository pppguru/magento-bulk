<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Components_Header extends Mage_Directory_Block_Data
implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
	
	public function getFilter($type)
	{
		$value = Mage::registry($type);
		return $value;
	}
	
	public function getFormKey()
	{
		return Mage::getSingleton('core/session')->getFormKey();
	}
}