<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Observer
{
    public function onCoreBlockAbstractToHtmlBefore($observer) 
    {
        $block = $observer->getBlock();
        $massactionClass  = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $productGridClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
        if ($massactionClass == get_class($block) && $productGridClass == get_class($block->getParentBlock())) {
            $types = Mage::getStoreConfig('ampaction/general/commands');
            if (!$types)
                return $this;
            
            $types = explode(',', $types);
            foreach ($types as $i => $type) {
                if (strlen($type) > 2) {
                    $command = Amasty_Paction_Model_Command_Abstract::factory($type);
                    $command->addAction($block);
                } else { // separator
                    $block->addItem('ampaction_separator' . $i, array(
                        'label'=> '---------------------',
                        'url'  => '' 
                    ));
                }
            }
        }
        
        return $this;
    }
}
