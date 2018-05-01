<?php

class MDN_AdvancedStock_Model_Sales_Order_Item extends Mage_Sales_Model_Order_Item {

    private $_preparationWarehouse = null;
    private $_erpOrderItem = null;


    /**
     * return erp order item
     */
    public function getErpOrderItem() {
        if ($this->_erpOrderItem == null) {
            $this->_erpOrderItem = Mage::getModel('AdvancedStock/SalesFlatOrderItem')->load($this->getId());
            $this->_erpOrderItem->setOrderItem($this);
        }
        return $this->_erpOrderItem;
    }

    /**
     * Retourne la marge pour cette ligne commande
     *
     */
    //todo: deporter
    public function GetMargin() {
        //Calcul la marge
        $retour = 0;
        $retour = ($this->getBasePrice() * $this->getqty_ordered()) - ($this->getData(mage::helper('purchase/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName()) * $this->getqty_ordered());

        return $retour;
    }

    /**
     * Retourne la marge en %
     *
     */
    //todo: deporter
    public function GetMarginPercent() {
        if ($this->getBasePrice() > 0)
            return ($this->getBasePrice() - $this->getData(mage::helper('purchase/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName())) / $this->getBasePrice() * 100;
        else
            return 0;
    }

    /**
     * when saving, update supply needs for product (if concerned)
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        //if order item juste created, create record in erp_sales_flat_order_item table
        if ($this->getId() != $this->getOrigData('item_id')) {
            Mage::getResourceModel('AdvancedStock/SalesFlatOrderItem')->initializeRecord($this);
        }

        $debug = '#After save on sales order item #' . $this->getId() . " : ";

        //mage::log($debug);
        //dispatch event
        Mage::dispatchEvent('salesorderitem_aftersave', array('salesorderitem' => $this));

        return $this;
    }

    /**
     * return real qty shipped (multiply with parent item)
     *
     */
    public function getRealShippedQty() {
        $qty = 0;

        //if no parent
        if ($this->getparent_item_id() == null) {
            $qty = $this->getqty_shipped();
        } else {
            //if has parent
            $parentItem = mage::getModel('sales/order_item')->load($this->getparent_item_id());
            if ($parentItem->isShipSeparately()) {
                $qty = $this->getqty_shipped();
            } else {
                $qty = $parentItem->getqty_shipped() * ($this->getqty_ordered() / $parentItem->getqty_ordered());
            }
        }

        return $qty;
    }

    /**
     * Return qty remaining to ship
     *
     */
    public function getRemainToShipQty() {
        $remainToShipQty = 0;

        //if no parent
        if ($this->getparent_item_id() == null) {
            switch (strtolower($this->getproduct_type())) {
                case null:
                case 'simple':
                case 'grouped':
                case 'giftcard':
                case 'virtual':
                case 'downloadable': 
                case 'configurable':
                    $remainToShipQty = $this->getqty_ordered() - $this->getqty_shipped() - $this->getqty_refunded() - $this->getqty_canceled();
                    break;
                case 'bundle':
                    if ($this->isShipSeparately())
                        $remainToShipQty = 0;
                    else
                        $remainToShipQty = $this->getqty_ordered() - $this->getqty_shipped() - $this->getqty_refunded() - $this->getqty_canceled();
                    break;
            }
        }
        else {
            //if has parent
            $parentItem = mage::getModel('sales/order_item')->load($this->getparent_item_id());
            if ($parentItem->isShipSeparately()) {
                $remainToShipQty = $this->getqty_ordered() - $this->getqty_shipped() - $this->getqty_refunded() - $this->getqty_canceled();
            } else {
                $remainToShipQty = $parentItem->getqty_ordered() - $parentItem->getqty_shipped() - $parentItem->getqty_refunded() - $parentItem->getqty_canceled();
                $remainToShipQty *= ( $this->getqty_ordered() / $parentItem->getqty_ordered());
            }
        }

        if ($remainToShipQty < 0)
            $remainToShipQty = 0;

        return $remainToShipQty;
    }

    /**
     * Return shelf location according to preparation warehouse
     */
    public function getShelfLocation() {
        $warehouse = $this->getPreparationWarehouse();
        if ($warehouse) {
            $stockItem = $warehouse->getProductStockItem($this->getproduct_id());
            return $stockItem->getshelf_location();
        }
        else
            return '';
    }

    /**
     * Return preparation warehouse
     */
    public function getPreparationWarehouse() {
        if ($this->_preparationWarehouse == null) {
            $this->_preparationWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($this->getpreparation_warehouse());
        }
        return $this->_preparationWarehouse;
    }

    /**
     * Lazy loading for erp order item datas
     */
    protected function _afterLoad() {

        parent::_afterLoad();

        if (!isset($this->data['esfoi_item_id'])) {
            $erpOrderItem = $this->getErpOrderItem();
            $this->setpreparation_warehouse($erpOrderItem->getpreparation_warehouse());
            $this->setreserved_qty($erpOrderItem->getreserved_qty());
            $this->setcomments($erpOrderItem->getcomments());
            $this->setesfoi_item_id($erpOrderItem->getId());
        }
    }

    public function getOrderItemOptions($lineReturn = "\n"){
        $buffer = '';
        if ($allOptions = $this->getData('product_options')) {
            $buffer .= $lineReturn;
            try {
                $options = unserialize($allOptions);
                if (isset($options['options'])) {
                    foreach ($options['options'] as $optionValues) {
                        if ($optionValues['value']) {
                            $buffer .= ' ' . $optionValues['label'] . ' : ';
                            $_printValue = isset($optionValues['print_value']) ? $optionValues['print_value'] : strip_tags($optionValues['value']);
                            $values = explode(', ', $_printValue);
                            foreach ($values as $value) {
                                if (is_array($value))
                                    foreach ($value as $_value)
                                        $buffer .= $_value;
                                else
                                    $buffer .= $value;
                            }
                            $buffer .= $lineReturn;
                        }
                    }
                }
            }catch(Exception $ex){
                mage::logException($ex);
            }
        }

        return $buffer;
    }

}