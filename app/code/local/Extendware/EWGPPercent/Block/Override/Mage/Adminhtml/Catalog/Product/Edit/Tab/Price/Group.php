<?php
class Extendware_EWGPPercent_Block_Override_Mage_Adminhtml_Catalog_Product_Edit_Tab_Price_Group extends Extendware_EWGPPercent_Block_Override_Mage_Adminhtml_Catalog_Product_Edit_Tab_Price_Group_Bridge
{
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('extendware/ewgppercent/catalog/product/edit/price/group.phtml');
        $this->getProduct()->getResource()->getAttribute('group_price')->getBackend()->loadEWData($this->getProduct());
        return $this;
    }
    
	public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        parent::setElement($element);
        $this->getElement()->setValue($this->getProduct()->getData('ewgroup_price'));
        $this->getElement()->setName('ewgroup_price');
        return $this;
    }
}
