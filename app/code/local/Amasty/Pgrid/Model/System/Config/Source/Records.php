<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Model_System_Config_Source_Records
{
    public function toOptionArray()
    {
        $options = array(
            array( 'value'  => '20', 'label' => Mage::helper('ampgrid')->__('20') ),
            array( 'value'  => '30', 'label' => Mage::helper('ampgrid')->__('30') ),
            array( 'value'  => '50', 'label' => Mage::helper('ampgrid')->__('50') ),
            array( 'value'  => '100', 'label' => Mage::helper('ampgrid')->__('100') ),
            array( 'value'  => '200', 'label' => Mage::helper('ampgrid')->__('200') ),
        );
        return $options;
    }
}
