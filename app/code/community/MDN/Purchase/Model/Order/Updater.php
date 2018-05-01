<?php

/**
 * This is class is dedicated to updated purchase order related datas
 *
 */
class MDN_Purchase_Model_Order_Updater extends Mage_Core_Model_Abstract {

    private $_orderId = null;
    private $_initialState = null;
    private $_endState = null;

    /**
     * Store initial status for order, items ...
     *
     */
    public function init($order) {
        //store inital order state
        $this->_initialState = $this->getOrderDataArray($order);
        return $this;
    }

    /**
     * Check for changes and launch updates
     *
     * @param unknown_type $order
     */
    public function checkForChangesAndLaunchUpdates($order) {
        //store end state
        $debug = '';
        $this->_endState = $this->getOrderDataArray($order);

        //###
        //check if we have to dispatch extended costs
        if ($this->orderInformationHasChanged('po_shipping_cost')
                || $this->orderInformationHasChanged('po_zoll_cost')
                || $this->hasProductAdded()
                || $this->hasProductQtyChanged()
                || $this->hasProductPriceChanged()
                || $this->hasProductDeleted()) {
            $order->dispatchExtendedCosts();
            $this->_endState = $this->getOrderDataArray($order);
            $debug .= 'dispatchExtendedCosts, ';
        }

        //###
        //check if we have to update suppliers associations
        if ($this->orderJustPassedToComplete()) {
            //if order just set to complete, update for all products
            $order->updateProductSupplierAssociation();
            $debug .= 'updateProductSupplierAssociation, ';
        } else {
            //else update association only for products for which information changed
            if ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_COMPLETE) {
                foreach ($this->_endState['products'] as $item) {
                    if ($this->productInformationHasChanged($item['pop_product_id'], 'pop_supplier_ref')
                            || $this->productInformationHasChanged($item['pop_product_id'], 'pop_price_ht')
                            || $this->productInformationHasChanged($item['pop_product_id'], 'pop_eco_tax')
                            || $this->productInformationHasChanged($item['pop_product_id'], 'pop_extended_costs')
                    ) {
                        $order->updateProductSupplierAssociation($item['pop_product_id']);
                        $debug .= 'updateProductSupplierAssociation for #' . $item['pop_product_id'] . ', ';
                    }
                }
            }
        }

        //###
        //check if we have to update product costs
        if ($this->orderJustPassedToComplete()) {
            //if order just set to complete, update for all products
            $order->UpdateProductsCosts();
            $debug .= 'UpdateProductsCosts, ';
        } else {
            //else, update product costs only for products for which cost information changed
            if ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_COMPLETE) {
                foreach ($this->_endState['products'] as $item) {
                    if (($this->productInformationHasChanged($item['pop_product_id'], 'pop_price_ht'))
                            || ($this->productInformationHasChanged($item['pop_product_id'], 'pop_eco_tax'))
                            || ($this->orderInformationHasChanged('po_currency_change_rate'))
                            || ($this->productInformationHasChanged($item['pop_product_id'], 'pop_extended_costs'))) {
                        $order->UpdateProductsCosts($item['pop_product_id']);
                        $debug .= 'UpdateProductsCosts for #' . $item['pop_product_id'] . ', ';
                    }
                }
            }
        }

        //##
        //check if we have to update delivery progress
        if ($this->hasProductAdded()
                || $this->hasProductQtyChanged()
                || $this->hasProductDeleted()
                || $this->hasProductDeliveredQtyChanged()) {
            $order->computeDeliveryProgress();
            $debug .= 'computeDeliveryProgress, ';
        }

        //check if we have to update products delivery date
        if ($this->orderJustPassedToComplete()
                || $this->orderJustPassedToWaitingForDelivery()
                || ($this->orderInformationHasChanged('po_supply_date') && ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY))) {
            $order->UpdateProductsDeliveryDate();
            $debug .= 'UpdateProductsDeliveryDate, ';
        } else {
            //else, if status is waiting for delivery, update delivery date for added or deleted products
            if ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY) {
                foreach ($this->getAddedOrDeletedProducts() as $productId) {
                    mage::helper('BackgroundTask')->AddTask('Update product delivery date for product #' . $productId,
                            'purchase',
                            'updateProductDeliveryDate',
                            $productId
                    );
                    $debug .= 'UpdateProductsDeliveryDate for product #' . $productId . ', ';
                }
            }
        }

        //check if product custom delivery date has change
        if ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY)
        {
            foreach ($this->_endState['products'] as $item) {
                if ($this->productInformationHasChanged($item['pop_product_id'], 'pop_delivery_date'))
                {
                    $productId = $item['pop_product_id'];
                    mage::helper('BackgroundTask')->AddTask('Update product delivery date for product #' . $productId,
                            'purchase',
                            'updateProductDeliveryDate',
                            $productId,
                            null,
                            true);
                }
            }
        }

        //Check if we have to update waiting for delivery qty
        if ($this->orderJustPassedToComplete()
                || $this->orderJustPassedToWaitingForDelivery()
                || $this->orderJustPassedFromWaitingForDelivery()) {
            $order->UpdateProductsWaitingForDeliveryQty();
            $debug .= 'UpdateProductsWaitingForDeliveryQty, ';
        } else {
            //else, if status is waiting for delivery, update delivery date for products for which qty has changed, added and deleted products
            if ($order->getpo_status() == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY) {
                $processedProducts = array();
                foreach ($this->getAddedOrDeletedProducts() as $productId) {
                    //plan task
                    mage::helper('BackgroundTask')->AddTask('Update waiting for delivery qty for product #' . $productId,
                            'purchase',
                            'updateProductWaitingForDeliveryQty',
                            $productId
                    );
                    $processedProducts[] = $productId;
                    $debug .= 'UpdateProductsWaitingForDeliveryQty for product #' . $productId . ', ';
                }
                foreach ($order->getProducts() as $product) {
                    if ($this->productInformationHasChanged($product->getpop_product_id(), 'pop_qty') || $this->productInformationHasChanged($product->getpop_product_id(), 'pop_supplied_qty')) {
                        if (!in_array($product->getpop_product_id(), $processedProducts)) {
                            $order->UpdateProductsWaitingForDeliveryQty($product->getpop_product_id());
                            $debug .= 'UpdateProductsWaitingForDeliveryQty for product #' . $product->getpop_product_id() . ', ';
                        }
                    }
                }
            }
        }

        if ($this->hasProductAdded())
        {
            $order->addHistory(Mage::helper('purchase')->__('Products added'));
        }

        return $debug;
    }

    //***************************************************************************************************************************************
    //***************************************************************************************************************************************
    // TOOLS
    //***************************************************************************************************************************************
    //***************************************************************************************************************************************

    private function getOrderDataArray($order) {
        $retour = array();

        $retour['po_id'] = $order->getId();
        $retour['po_supply_date'] = $order->getpo_supply_date();
        $retour['po_shipping_cost'] = $order->getpo_shipping_cost();
        $retour['po_zoll_cost'] = $order->getpo_zoll_cost();
        $retour['po_tax_rate'] = $order->getpo_tax_rate();
        $retour['po_status'] = $order->getpo_status();
        $retour['po_currency_change_rate'] = $order->getpo_currency_change_rate();

        //store products information
        $retour['products'] = array();
        foreach ($order->getProducts() as $item) {
            $productData = array();
            $productData['pop_product_id'] = $item->getpop_product_id();
            $productData['pop_qty'] = $item->getpop_qty();
            $productData['pop_supplied_qty'] = $item->getpop_supplied_qty();
            $productData['pop_price_ht'] = $item->getpop_price_ht();
            $productData['pop_supplier_ref'] = $item->getpop_supplier_ref();
            $productData['pop_eco_tax'] = $item->getpop_eco_tax();
            $productData['pop_extended_costs'] = $item->getpop_extended_costs();
            $productData['pop_delivery_date'] = $item->getpop_delivery_date();

            $retour['products'][$item->getpop_product_id()] = $productData;
        }

        return $retour;
    }

    /**
     * Check if an order information has changed between initial and end state
     *
     * @param unknown_type $informationCode
     * @return unknown
     */
    private function orderInformationHasChanged($informationCode) {
        return ($this->_initialState[$informationCode] != $this->_endState[$informationCode]);
    }

    /**
     * Return true if an product information changed
     *
     * @param unknown_type $productId
     * @param unknown_type $informationCode
     */
    private function productInformationHasChanged($productId, $informationCode) {


        if ((!isset($this->_initialState['products'][$productId])) || (!isset($this->_endState['products'][$productId])))
            return true;

        if ($this->_initialState['products'][$productId][$informationCode] != $this->_endState['products'][$productId][$informationCode])
            return true;

        return false;
    }

    /**
     * Return true if a product has been added
     *
     */
    private function hasProductAdded() {
        //parse end products
        foreach ($this->_endState['products'] as $endProduct) {
            if (!isset($this->_initialState['products'][$endProduct['pop_product_id']]))
                return true;
        }
        return false;
    }

    /**
     * Check if a product qty has changed
     *
     */
    private function hasProductQtyChanged() {
        //parse end products
        foreach ($this->_endState['products'] as $endProduct) {
            if (isset($this->_initialState['products'][$endProduct['pop_product_id']])) {
                if ($endProduct['pop_qty'] != $this->_initialState['products'][$endProduct['pop_product_id']]['pop_qty'])
                    return true;
            }
        }
        return false;
    }

    /**
     * Check if a product price has changed
     *
     */
    private function hasProductPriceChanged() {
        //parse end products
        foreach ($this->_endState['products'] as $endProduct) {
            if (isset($this->_initialState['products'][$endProduct['pop_product_id']])) {
                if ($endProduct['pop_price_ht'] != $this->_initialState['products'][$endProduct['pop_product_id']]['pop_price_ht'])
                    return true;
            }
        }
        return false;
    }

    /**
     * Check if a product delivered qty has changed
     *
     */
    private function hasProductDeliveredQtyChanged() {
        //parse end products
        foreach ($this->_endState['products'] as $endProduct) {
            if (isset($this->_initialState['products'][$endProduct['pop_product_id']])) {
                if ($endProduct['pop_supplied_qty'] != $this->_initialState['products'][$endProduct['pop_product_id']]['pop_supplied_qty'])
                    return true;
            }
        }
        return false;
    }

    /**
     * Check if a product has been removed
     *
     */
    private function hasProductDeleted() {
        foreach ($this->_initialState['products'] as $initialProduct) {
            if (!isset($this->_endState['products'][$initialProduct['pop_product_id']]))
                return true;
        }
    }

    /**
     * Check if order just passed to complete status
     *
     */
    private function orderJustPassedToComplete() {
        if (($this->_endState['po_status'] == MDN_Purchase_Model_Order::STATUS_COMPLETE) && ($this->_initialState['po_status'] != MDN_Purchase_Model_Order::STATUS_COMPLETE))
            return true;
        else
            return false;
    }

    /**
     * Check if order just passed to complete status
     *
     */
    private function orderJustPassedToWaitingForDelivery() {
        if (($this->_endState['po_status'] == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY) && ($this->_initialState['po_status'] != MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY))
            return true;
        else
            return false;
    }

    /**
     * Check if order comes from waiting for delivery status
     *
     */
    private function orderJustPassedFromWaitingForDelivery() {
        if (($this->_initialState['po_status'] == MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY) && ($this->_endState['po_status'] != MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY))
            return true;
        else
            return false;
    }

    /**
     * Return added or deleted product ids
     *
     */
    private function getAddedOrDeletedProducts() {
        $retour = array();

        //check for added products
        foreach ($this->_endState['products'] as $endProduct) {
            if (!isset($this->_initialState['products'][$endProduct['pop_product_id']]))
                $retour[] = $endProduct['pop_product_id'];
        }

        //check deleted products
        foreach ($this->_initialState['products'] as $initialProduct) {
            if (!isset($this->_endState['products'][$initialProduct['pop_product_id']]))
                $retour[] = $initialProduct['pop_product_id'];
        }

        return $retour;
    }

}