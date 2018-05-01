<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_System_Config_Source_Countries
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
