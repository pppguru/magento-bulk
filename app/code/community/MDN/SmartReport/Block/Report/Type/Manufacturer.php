<?php


class MDN_SmartReport_Block_Report_Type_Manufacturer extends MDN_SmartReport_Block_Report_Type
{
    protected $_manufacturer = null;

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Manufacturer').' '.$this->getManufacturer()->getName();
    }

    public function getManufacturer()
    {
        if ($this->_manufacturer == null)
        {
            $vars = $this->getVariables();

            $this->_manufacturer = new Varien_Object();
            $this->_manufacturer->setId($vars['manufacturer_id']);

            $manufacturerName = Mage::helper('SmartReport/Attribute')->getAttributeValueLabel(Mage::helper('SmartReport')->getManufacturerAttributeCode(), $vars['manufacturer_id']);

            $this->_manufacturer->setName($manufacturerName);
        }
        return $this->_manufacturer;
    }

    public function getManufacturers()
    {
        $manufacturers = Mage::helper('SmartReport/Attribute')->getAttributeValues(Mage::helper('SmartReport')->getManufacturerAttributeCode(), false);

        return $manufacturers;
    }


    public function getAdditionalFilters()
    {
        $filters = array();

        $countryFilter = '<select name="manufacturer_id" style="margin-right: 20px;">';
        foreach($this->getManufacturers() as $c)
        {
            $selected = ($c['value'] == $this->getManufacturer()->getId() ? ' selected ' : '');
            $countryFilter .= '<option '.$selected.' value="'.$c['value'].'">'.$c['label'].'</option>';
        }
        $countryFilter .= '</select>';

        $filters['Manufacturer'] = $countryFilter;

        return $filters;
    }

    public function getFormHiddens()
    {
        return array('manufacturer_id' => $this->getManufacturer()->getId());
    }

    public function canDisplay()
    {
        $vars = $this->getVariables();
        return $vars['manufacturer_id'];
    }

}