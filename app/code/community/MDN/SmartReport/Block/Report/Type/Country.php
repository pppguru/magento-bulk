<?php


class MDN_SmartReport_Block_Report_Type_Country extends MDN_SmartReport_Block_Report_Type
{
    protected $_country = null;

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Country').' '.$this->getCountry()->getName();
    }

    public function getCountry()
    {
        if ($this->_country == null)
        {
            $vars = $this->getVariables();
            if (!isset($vars['country_id']))
                $vars['country_id'] = Mage::getStoreConfig('general/country/default');
            $this->_country = Mage::getModel('directory/country')->load($vars['country_id']);
        }
        return $this->_country;
    }

    public function getCountries()
    {
        return Mage::getSingleton('adminhtml/System_config_source_country')->toOptionArray();
    }


    public function getAdditionalFilters()
    {
        $filters = array();

        $countryFilter = '<select name="country_id" style="margin-right: 20px;">';
        foreach($this->getCountries() as $c)
        {
            $selected = ($c['value'] == $this->getCountry()->getId() ? ' selected ' : '');
            $countryFilter .= '<option '.$selected.' value="'.$c['value'].'">'.$c['label'].'</option>';
        }
        $countryFilter .= '</select>';

        $filters['Country'] = $countryFilter;

        return $filters;
    }

    public function getFormHiddens()
    {
        return array('country_id' => $this->getCountry()->getId());
    }

    public function canDisplay()
    {
        $vars = $this->getVariables();
        return isset($vars['country_id']);
    }

}