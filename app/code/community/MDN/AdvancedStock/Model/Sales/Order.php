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
class MDN_AdvancedStock_Model_Sales_Order extends Mage_Sales_Model_Order
{

    /**
     * Rewrite get items collection to join with the erp_sales_flat_order_item table
     *
     * @param <type> $filterByTypes
     * @param <type> $nonChildrenOnly
     * @return <type>
     */
    public function getItemsCollection($filterByTypes = array(), $nonChildrenOnly = false)
    {
        if (is_null($this->_items)) {
            $this->_items = Mage::getResourceModel('sales/order_item_collection')
                ->setOrderFilter($this);

            //join with erp_sales_flat_order_item
            $this->_items->joinErpTable();

            if ($filterByTypes) {
                $this->_items->filterByTypes($filterByTypes);
            }
            if ($nonChildrenOnly) {
                $this->_items->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setOrder($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * get total margin
     *
     */
    public function getMargin()
    {
        $retour = 0;
        foreach ($this->getAllVisibleItems() as $item) {
            $retour += $item->getMargin();
        }
        return $retour;
    }

    /**
     * get total margin in percent
     *
     */
    public function getMarginPercent()
    {
        if ($this->getsubtotal() > 0)
            return ($this->getMargin()) / $this->getbase_subtotal() * 100;
        else
            return 0;
    }

    /**
     * Return true if all product are reserved
     *
     * @return unknown
     */
    public function IsFullStock($warehouseId = null)
    {
        foreach ($this->getItemsCollection() as $item) {
            if (($warehouseId != null) && ($warehouseId != $item->getpreparation_warehouse()))
                continue;

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getproduct_id());
            if ($stockItem) {
                if ($stockItem->getManageStock()) {
                    $remaining_qty = $item->getRemainToShipQty();
                    if (($item->getreserved_qty() < $remaining_qty) && ($remaining_qty > 0)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Return true if all products are reserved
     *
     */
    public function allProductsAreReserved()
    {
        foreach ($this->getItemsCollection() as $item) {
            $product = mage::getModel('catalog/product')->load($item->getproduct_id());
            $manageStock = true;
            if ($product->getId())
                $manageStock = $product->getStockItem()->getManageStock();
            if ($manageStock) {
                $remaining_qty = $item->getRemainToShipQty() - $item->getreserved_qty();
                if ($remaining_qty > 0)
                    return false;
            }
        }

        return true;
    }

    /**
     * Return true if an order is completely shipped
     *
     */
    public function IsCompletelyShipped()
    {
        //recupere la liste des produits de la commande
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getRemainToShipQty() > 0)
                return false;
        }

        return true;
    }

    /**
     *
     *
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        //update order is valid
        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {

            mage::helper('AdvancedStock/Sales_ValidOrders')->updateIsValid($this);
        }

        Mage::dispatchEvent('salesorder_beforesave', array('order' => $this));
    }

    /**
     * Reserve of Un reserve product if validity status change
     *
     * If order is cancelled, unrerved product
     *
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        Mage::dispatchEvent('salesorder_aftersave', array('order' => $this));

        //if order just being created, exit
        if (!$this->getOrigData('entity_id'))
            return;

        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {

            //if order is_valid change, update stock information for products
            if ($this->getis_valid() != $this->getOrigData('is_valid')) {


                foreach ($this->getAllItems() as $item) {

                    //unreserve if necessary
                    if (mage::getStoreConfig('advancedstock/valid_orders/do_not_consider_invalid_orders_for_stocks')) {
                        if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($this)) {
                            mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($this, $item);
                        }
                    }

                    //Will try to reserve in all case by backgroudn task
                    mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), ' from order validity change ');
                }
            }


            if ($this->getstate() != $this->getOrigData('state')) {

                //if order has been canceled, update products stocks and reserved qties
                if ($this->getstate() == Mage_Sales_Model_Order::STATE_CANCELED) {
                    foreach ($this->getAllItems() as $item) {
                        mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($this, $item);
                        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order is cancel event');
                    }
                    Mage::dispatchEvent('salesorder_just_cancelled', array('order' => $this));
                }

                //case partial cancel order on cancel order after partial shipment
                if ($this->getstate() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    foreach ($this->getAllItems() as $item) {

                        if ($item->getStatusId() == Mage_Sales_Model_Order_Item::STATUS_CANCELED) {
                            //unreserve product
                            mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($this, $item);

                            //plan product stocks update
                            mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order item is cancel event');
                        }
                    }
                }

                //to make order modified in Magento to disappear from order preparation on some important state change
                if ($this->getstate() == Mage_Sales_Model_Order::STATE_COMPLETE
                    || $this->getstate() == Mage_Sales_Model_Order::STATE_CANCELED
                    || $this->getstate() == Mage_Sales_Model_Order::STATE_CLOSED
                ) {

                    if (!$this->isBeingDispatched()) {

                        mage::helper('BackgroundTask')->AddTask(
                            'Dispatch order #' . $this->getId() . ' (on Order State Changed)',
                            'Orderpreparation',
                            'dispatchOrder',
                            $this->getId()
                        );
                    }
                }
            }

        }


    }

    /**
     * Define if ERP can reserve products for this order
     *
     */
    public function productReservationAllowed()
    {

        $reservationAllowed = true;
        if (mage::getStoreConfig('advancedstock/valid_orders/do_not_consider_invalid_orders_for_stocks')) {
            if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($this)) {
                $reservationAllowed = false;
            }
        }
        return $reservationAllowed;
    }

    /**
     * Return order date (depending of magento version)
     *
     * @return unknown
     */
    public function getOrderPlaceDate()
    {
        $value = $this->getCreatedAtStoreDate();
        if ($value == '')
            $value = $this->getcreated_at();
        return $value;
    }

    /**
     * Return preparation warehouses depending of order item
     */
    public function getPreparationWarehouses()
    {
        $warehouseIds = array();
        foreach ($this->getAllItems() as $item) {
            if ($item->getpreparation_warehouse())
                $warehouseIds[] = $item->getpreparation_warehouse();
        }

        $collection = mage::getModel('AdvancedStock/Warehouse')
            ->getCollection()
            ->addFieldToFilter('stock_id', array('in' => $warehouseIds));
        return $collection;
    }

    public function isBeingDispatched()
    {
        $isBeingPrepared = Mage::getModel('Orderpreparation/ordertoprepare')->getCollection()->addFieldToFilter('order_id', $this->getId())->getFirstItem();
        if (!$isBeingPrepared->getId())
            return false;
        else
        {
            $user = Mage::getModel('admin/user')->load($isBeingPrepared->getuser());
            return $user->getusername();
        }
    }

    public function canCancel()
    {
        if ($this->isBeingDispatched()) {
            return false;
        }

        return parent::canCancel();
    }

    /**
     * When the order is deleted by a tiers-party extension,
     * -> release stock reservation as possible
     */
    protected function _beforeDelete()
    {
        //STOCK MANAGEMENT

        //clean product reservation
        foreach ($this->getAllItems() as $item) {

            if($item->getproduct_id()) {
                //release stock in all cases
                mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($this, $item);

                //force stock update
                mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), ' on delete order #'.$this->getIncrementId());
            }
        }

    }

    /**
     * Once the order is deleted by a tiers-party extension,
     * -> clean erp related tables
     */
    protected function _afterDelete()
    {
        //ORDER PREPARATION SCREEN

        //clean selected order
        $selectedOrderItem = Mage::getModel('Orderpreparation/ordertoprepare')->getCollection()->addFieldToFilter('order_id', $this->getId())->getFirstItem();
        if ($selectedOrderItem->getId()){
            $selectedOrderItem->delete();
        }

        //clean full stock, stock less, ignored orders tabs
        $orderPreparationItem = Mage::getModel('Orderpreparation/ordertopreparepending')->getCollection()->addFieldToFilter('opp_order_id', $this->getId())->getFirstItem();
        if ($orderPreparationItem->getId()){
            $orderPreparationItem->delete();
        }


    }

}