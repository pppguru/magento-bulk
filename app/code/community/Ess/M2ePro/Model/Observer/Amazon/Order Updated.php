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
		
	    /** @var $magentoOrder Mage_Sales_Model_Order */
        $magentoOrder = $observer->getEvent()->getMagentoOrder();

        foreach ($magentoOrder->getAllItems() as $orderItem) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getHasChildren()) {
                continue;
            }

            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($orderItem->getProductId());

            if (!$stockItem->getId()) {
                continue;
            }
			
			$coreResource = Mage::getSingleton('core/resource');
			
			$write = $coreResource->getConnection('core_write');
			
			Mage::log($stockItem->getQty(),null,'before_qry.txt');
			
			$resqty = $stockItem->getStockReservedQty();
			
			if($resqty!=0) 
				$dbqty = $orderItem->getQtyOrdered() * 2 ;
			else
				$dbqty = $orderItem->getQtyOrdered() ;
			
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