<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/ 
class Amasty_Paction_Model_Source_Direction
{
    public function toOptionArray()
    {
        $options = array();
        
        // magento wants at least one option to be selected
        $options[] = array(
            'value' => '0',
            'label' => Mage::helper('ampaction')->__('Selected to IDs'),
            
        );
        $options[] = array(
            'value' => '1',
            'label' => Mage::helper('ampaction')->__('IDs to Selected'),
            
        );
        return $options;
    }
}
