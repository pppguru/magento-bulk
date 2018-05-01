<?php

/**
 * @Author Mohin
 */
class Bulksupplements_RestrictCountry_Model_System_Config_Source_Countries
    extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     *
     * @return array
     */
    public function getAllOptions()
    {
        return Mage::getResourceModel('directory/country_collection')
            ->loadData()
            ->toOptionArray(false);
    }
}
