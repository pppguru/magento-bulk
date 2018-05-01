<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
*/
class Amasty_Number_Model_Observer
{
    public function processBlockHtmlBefore($observer) 
    {
//        $block = $observer->getBlock();
// rewrite all 4 grids to set text type for increments
// add compatibility with extenede order grid
        
//        $productGridClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
//        if ($productGridClass == get_class($block)) {
//
//  ->getColumn()->setType(text);
//        }
        
        return $this;
    }
    
    public function processDocumentSaveBefore($observer)
    {
        $type = '';
        foreach (array('invoice', 'shipment', 'creditmemo') as $t){
            if (is_object($observer->getData($t))){
                $type = $t;
            }
        }
        
        if (!$type){
             return;   
        }
        
        $doc = $observer->getData($type);
        if ($doc->getId()) { // do not need change the `Increment Id` if a document is not new
            return;
        }
        
        $order   = $doc->getOrder();
        $storeId = $order->getStore()->getStoreId();
        
        if ( !Mage::getStoreConfig('amnumber/' . $type . '/same', $storeId)){
            return;
        }
        
        $number  = 0;
        $counter = 0;
        while (!$number) {
            $number  = $order->getIncrementId();
            $prefix  = Mage::getStoreConfig('amnumber/' . $type . '/prefix', $storeId);

            $replace = Mage::getStoreConfig('amnumber/' . $type . '/replace', $storeId);
            if ($replace){
                $number = str_replace($replace, $prefix, $number);
            }
            else {
                $number = $prefix . $number;
            }
            
             
            if ($counter) {
                $number .= '-' . $counter;
            }
            
            $collection = Mage::getModel('sales/order_' . $type)
                ->getCollection()
                ->addFieldToFilter('increment_id', $number)
                ->setPageSize(1);
            
            if (count($collection)){
                $number = 0;
            }
            
            ++$counter; 
        }
        
        $doc->setIncrementId($number);
    }
} 
