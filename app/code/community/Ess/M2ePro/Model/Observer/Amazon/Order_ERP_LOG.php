<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Amazon_Order
{
    //####################################

    public function revertOrderedQty(Varien_Event_Observer $observer)
    {
		Mage::log('welcome',null,'welcome.txt');
		
        $erp_debug_log = 'revertOrderedQty begin';
        
	    /** @var $magentoOrder Mage_Sales_Model_Order */
        $magentoOrder = $observer->getEvent()->getMagentoOrder();
        
        $erp_debug_log .= "\n".' for order Id = '.$magentoOrder->getId().' IncId='.$magentoOrder->getIncrementId();
        
        
        foreach ($magentoOrder->getAllItems() as $orderItem) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getHasChildren()) {
                continue;
            }

            $erp_debug_log .= "\n".' looking for product ID='.$orderItem->getProductId();
            
            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($orderItem->getProductId());

            if (!$stockItem->getId()) {
                $erp_debug_log .= "\n".' product ID='.$orderItem->getProductId().' skipped because there is no stock entry';
                continue;
            }
			
			$coreResource = Mage::getSingleton('core/resource');
			
			$write = $coreResource->getConnection('core_write');
            
            $erp_debug_log .= "\n".' product ID='.$orderItem->getProductId().' stock was '.$stockItem->getQty();
			
			Mage::log($stockItem->getQty(),null,'before_qry.txt');
			
			$dbqty = $orderItem->getQtyOrdered() * 2 ;
			$new_quantity = $dbqty + $stockItem->getQty();
			
			$product_id = $orderItem->getProductId();
			
			Mage::log($magentoOrder->getId(),null,'order_id.txt');
			
			Mage::log($new_quantity,null,'new_quantity.txt');
			
			$write->query( "UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status status_stock SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = IF('$new_quantity'>0, 1,0),
	status_stock.qty = '$new_quantity', status_stock.stock_status = IF('$new_quantity'>0, 1,0)
	WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id" );
	   		
			$stockUpdatedItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($orderItem->getProductId());
				
			//$this->updateAmazonFbaProductQty($write,$orderItem->getProductId(),$new_quantity);	
			Mage::log($stockUpdatedItem->getQty(),null,'after_qry.txt');
            
            $erp_debug_log .= "\n".' product ID='.$stockUpdatedItem->getProductId().' stock si after sql update '.$stockUpdatedItem->getQty();
					
			//Mage::getModel('cataloginventory/stock')->backItemQty($orderItem->getProductId(),$orderItem->getQtyOrdered()); 
            //$stockItem->addQty($orderItem->getQtyOrdered())->save();
            
            //PATCH BY ERP
            //COMMENT FROM LINE 22 to 30
            //and use that instead
            
			//Mage::log($stockItem->getQty(),null,'qty1.txt');
			//Mage::log($orderItem->getStockItem()->getQty(),null,'qty2.txt');
            
/*          $sourceWarehouseId = null;
            $targetWarehouseId = 1;
            $qty = $orderItem->getQtyOrdered();
            $additionalData = array('sm_type' => 'adjustment');
            mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                        $orderItem->getProductId(),
                        $sourceWarehouseId,
                        $targetWarehouseId,
                        $qty,
                        mage::helper('AdvancedStock')->__('Adjustment').' from revertOrderedQty : new Qty=',
                        $additionalData);
                       */            
            //
        }
        
        Mage::log($erp_debug_log,null,'erp_m2epro_revertOrderedQty_monitoring.log');
    }
	
	public function updateAmazonFbaProductQty($database, $product_id, $new_quantity) {
		$qry = "UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status status_stock
       SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = IF('$new_quantity'>0, 1,0),
       status_stock.qty = '$new_quantity', status_stock.stock_status = IF('$new_quantity'>0, 1,0)
       WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id ";
	   
	   Mage::log($qry,null,'qry.txt');
 		$database->query ( "UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status 

status_stock
       SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = IF('$new_quantity'>0, 1,0),
       status_stock.qty = '$new_quantity', status_stock.stock_status = IF('$new_quantity'>0, 1,0)
       WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id " );
	   
     }

    //####################################
}