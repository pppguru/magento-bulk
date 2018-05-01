<?php
class Extendware_EWGPPercent_Block_Override_Mage_Adminhtml_Catalog_Product_Edit_Tab_Price_Tier extends Extendware_EWGPPercent_Block_Override_Mage_Adminhtml_Catalog_Product_Edit_Tab_Price_Tier_Bridge
{
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('extendware/ewgppercent/catalog/product/edit/price/tier.phtml');
        $this->getProduct()->getResource()->getAttribute('tier_price')->getBackend()->loadEWData($this->getProduct());
        return $this;
    }
    
	public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        parent::setElement($element);
        $this->getElement()->setValue($this->getProduct()->getData('ewtier_price'));
        $this->getElement()->setName('ewtier_price');
        return $this;
    }
}
