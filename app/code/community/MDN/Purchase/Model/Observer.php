<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Model_Observer {


    /**
     * called on product duplication
     * @param Varien_Event_Observer $observer
     */
    public function catalog_model_product_duplicate(Varien_Event_Observer $observer) {
        $newProduct = $observer->getEvent()->getnew_product();

        //reset out of stock period
        $newProduct->setsupply_date();
        $newProduct->setwaiting_for_delivery_qty(0);
        $newProduct->setmanual_supply_need_date();
        $newProduct->setmanual_supply_need_comments('');
        $newProduct->setmanual_supply_need_qty(0);

    }
    
    /**
     * Delete relative data when a product is deleting in magento
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalog_product_delete_before(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();        
        if($productId>0){
            //Delete product's suppliers association
            $psCollection = mage::getModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->addFieldToFilter('pps_product_id', $productId);
            foreach ($psCollection as $ps){
                $ps->delete();
            }

            //Delete Purchase Packaging            
            $ppCollection = mage::getModel('Purchase/Packaging')
                        ->getCollection()
                        ->addFieldToFilter('pp_product_id', $productId);
            foreach ($ppCollection as $pp){
                $pp->delete();
            }
        }
    }

}

