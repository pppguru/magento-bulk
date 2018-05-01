<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/ 
class Amasty_Paction_Model_Source_Commands
{
    public function toOptionArray()
    {
        $options = array();
        
        // magento wants at least one option to be selected
        $options[] = array(
            'value' => '',
            'label' => '',
            
        );
        $types = array('', 'addcategory', 'removecategory', 'replacecategory',
            '', 'modifycost', 'modifyprice', 'modifyspecial', 'addspecial', 'addprice', 'addspecialbycost',
            '', 'relate', 'upsell', 'crosssell',
            '', 'unrelate', 'unupsell', 'uncrosssell',
            '', 'copyoptions', 'copyattr', 'copyimg', 'removeimg',
            '', 'changeattributeset',
            '', 'delete',
            '');
        foreach ($types as $i => $type) {
            if ($type) {
                $command = Amasty_Paction_Model_Command_Abstract::factory($type);
                $options[] = array(
                    'value' => $type,
                    'label' => Mage::helper('ampaction')->__($command->getLabel()),
                );
            } else {
                $options[] = array(
                    'value' => $i,
                    'label' => '---------------------',
                );
            }
        }   
        return $options;
    }
}
