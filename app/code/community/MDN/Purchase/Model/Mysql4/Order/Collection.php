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
/**
 * Collection de quotation
 *
 */
class MDN_Purchase_Model_Mysql4_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Purchase/Order');
    }
    
    public function getFullList()
    {
			
		 $this->getSelect()
    		->join('purchase_supplier', 'po_sup_num=sup_id')
    		->joinLeft('purchase_order_product', 'po_num=pop_order_num', array('SUM(pop_qty-pop_supplied_qty)'=>'SUM(pop_qty-pop_supplied_qty)'))
            ->group('po_num');
           
        return $this;
    }
    

    public function getSelect()
    {
        return $this->_select;
    }
    
    
    /**
     * Return remaining from purchase order with status = waiting_for_delivery
     */
    public function getRemainingSupplyQuantities()
    {           

        $productNameAttributeId = mage::getModel('Purchase/Constant')->GetProductNameAttributeId();
        $manufacturerAttributeId = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeId();

    	$select = $this->getSelect()
    		->reset()
            ->from(array('purchase_order_product'=>$this->getTable('OrderProduct')), array('pop_product_id'))
            ->joinLeft(array('purchase_order'=>$this->getTable('Order')),
                          'purchase_order.po_num=purchase_order_product.pop_order_num',
                          array('SUM((pop_qty - pop_supplied_qty)) AS expected_qty', 'MIN(po_supply_date) AS older_date'))            
            ->joinLeft(array('catalog_product'=>$this->getTable('catalog/product')),
                          'catalog_product.entity_id=purchase_order_product.pop_product_id',
                          'sku')            
            ->joinLeft(array('catalog_product_name'=>$this->getTable('CatalogProductVarchar')),
                          'catalog_product_name.entity_id=catalog_product.entity_id and catalog_product_name.store_id = 0 and catalog_product_name.attribute_id = '.$productNameAttributeId,
                          array('name' => 'value'));

            if($manufacturerAttributeId>0){
                $select->joinLeft(array('catalog_product_manufacturer'=>$this->getTable('CatalogProductInt')),
                              'catalog_product_manufacturer.entity_id=purchase_order_product.pop_product_id and catalog_product_manufacturer.store_id = 0 and catalog_product_manufacturer.attribute_id = '.$manufacturerAttributeId,
                              array('manufacturer' => 'value'));
            }
        
            $select->where('purchase_order.po_status=? and (pop_qty - pop_supplied_qty) > 0', MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY)
            ->group(array('pop_product_id', 'sku'));
                    
        return $this;

    }
    
    /**
     * Return purchase order for 1 product having remaining qties
     *
     * @param unknown_type $productId
     */
    public function getPendingOrdersForProduct($productId)
    {
    	$select = $this->getSelect()
    		->reset()
            ->from(array('purchase_order'=>$this->getTable('Order')), array('*'))
            ->joinLeft(array('supplier'=>$this->getTable('Supplier')),
                          'sup_id = po_sup_num',
                          array('*'))
            ->join(array('purchase_order_product'=>$this->getTable('OrderProduct')),
                          'po_num = pop_order_num and pop_product_id = '.$productId,
                          array('*'))
			->where('purchase_order.po_status = ?', MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY);            
        return $this;
    	
    }
    
}