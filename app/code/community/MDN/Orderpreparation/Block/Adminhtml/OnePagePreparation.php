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
class MDN_Orderpreparation_Block_Adminhtml_OnePagePreparation extends Mage_Adminhtml_Block_Widget_Form {

    private $_currentOrder = null;
    private $_orderCollection = null;
    private $_orderPreparationItem = null;
    private $_shipment = null;
    private $_invoice = null;

    /**
     * Return current order
     *
     * @return unknown
     */
    public function getCurrentOrder() {
        return $this->_currentOrder;
    }

    /**
     * return order preparation item
     *
     * @return unknown
     */
    public function getOrderPreparationItem() {
        if ($this->_orderPreparationItem == null) {
            $this->_orderPreparationItem = mage::getModel('Orderpreparation/Ordertoprepare')->load($this->getCurrentOrder()->getId(), 'order_id');
        }
        return $this->_orderPreparationItem;
    }

    /**
     * Set current order
     *
     */
    public function setCurrentOrder($order) {
        $this->_currentOrder = $order;
    }

    /**
     * return invoice
     *
     */
    public function getInvoice() {
        if ($this->_invoice == null) {
            $incrementId = $this->getOrderPreparationItem()->getinvoice_id();
            if ($incrementId)
                $this->_invoice = mage::getModel('sales/order_invoice')->loadByIncrementId($incrementId);
        }
        return $this->_invoice;
    }

    /**
     * return shipment
     *
     */
    public function getShipment() {
        if ($this->_shipment == null) {
            $incrementId = $this->getOrderPreparationItem()->getshipment_id();
            if ($incrementId)
                $this->_shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($incrementId);
        }
        return $this->_shipment;
    }

    /**
     * return order to prepare collection
     *
     * @return unknown
     */
    private function getOrderCollection() {
        if ($this->_orderCollection == null)
            $this->_orderCollection = mage::helper('Orderpreparation/OnePagePreparation')->getOrderList('*');
        return $this->_orderCollection;
    }

    /**
     * return order list as combo
     *
     * @param unknown_type $name
     * @param unknown_type $onchange
     * @return unknown
     */
    public function getOrderListAsCombo($name, $onchange) {
        $retour = '<select name="' . $name . '" id="' . $name . '" onchange="' . $onchange . '">';
        foreach ($this->getOrderCollection() as $item) {
            $selected = '';
            if ($this->getCurrentOrder()->getId() == $item->getorder_id())
                $selected = ' selected ';
            $value = $this->getUrl('*/*/index', array('force_order_id' => $item->getorder_id()));
            $comments = '';
            if ($item->getinvoice_id() != '')
                $comments = ' (invoice #' . $item->getinvoice_id() . ')';
            if ($item->getshipment_id() != '')
                $comments .= ' (shipment #' . $item->getshipment_id() . ')';
            if ($comments != '')
                $comments = ' - ' . $comments;
            $retour .= '<option value="' . $value . '" ' . $selected . '>' . $this->__('Order #') . $item->getincrement_id() . ' - ' . $item->getshipping_name() . ' - ' . $comments . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * return progress
     *
     * @return unknown
     */
    public function getProgress() {
        $count = $this->getOrderCollection()->getSize();
        $current = 0;
        $pos = 0;
        foreach ($this->getOrderCollection() as $item) {
            if ($item->getorder_id() == $this->getCurrentOrder()->getId()) {
                $current = $pos;
                break;
            }
            $pos++;
        }
        return ($current + 1) . ' / ' . $count;
    }

    /**
     * return service type as combo
     *
     * @param unknown_type $name
     */
    public function getServiceTypeAsCombo($name) {
        $CurrentValue = $this->getOrderPreparationItem()->getship_mode();
        $Carrier = $this->getCurrentOrder()->getshipping_method();

        if ($this->getShipment())
            return $CurrentValue;

        //cree le menu
        $retour = '<select id="' . $name . '" name="' . $name . '">';
        $model = mage::Helper('Orderpreparation')->getCarrierModel($Carrier);
        if ($model) {
            $values = $model->GetProductTypes();
            $retour .= '<option value=""></option>';
            foreach ($values as $key => $value) {
                $retour .= '<option value="' . $key . '"';
                if ($key == $CurrentValue)
                    $retour .= ' selected ';
                $retour .= '>' . $value . '</option>';
            }
        }
        else
            $retour .= '<option>No carrier for ' . $Carrier . ' </option>';
        $retour .= '</select>';
        return $retour;
    }

    /**
     * return collection of products shippable (or shipped)
     *
     * @return unknown
     */
    public function getItemsToShip() {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($this->getCurrentOrder()->getId());
        foreach ($collection as $item) {
            $OrderItem = mage::getModel('sales/order_item')->load($item->getorder_item_id());
            $item->setorder_item($OrderItem);
        }
        return $collection;
    }

    /**
     * return combobox to select qty
     *
     * @param unknown_type $product
     * @return unknown
     */
    public function getQtyCombo($product, $name) {
        $retour = '';
        if ($this->getShipment())
            $retour = $product->getqty();
        else {
            $min = 0;
            $max = $product->getorder_item()->getqty_ordered() - $product->getorder_item()->getRealShippedQty() - $product->getorder_item()->getqty_canceled();

            //return combo box if product is simple without parent
            $retour = '<select name="' . $name . '" id="' . $name . '" >';
            for ($i = $min; $i <= $max; $i++) {
                if ($i == $product->getqty())
                    $selected = ' selected ';
                else
                    $selected = ' ';
                $retour .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
            }
            $retour .= '</select>';
        }
        return $retour;
    }

    /**
     * set if user can change product qty
     *
     * @param unknown_type $product
     * @return unknown
     */
    public function canEditQty($product) {
        $retour = true;

        //prevent from changing qty if parent is bundle and is ship together
        if ($product->getorder_item()->getparent_item_id()) {
            $parent = mage::getModel('sales/order_item')->load($product->getorder_item()->getparent_item_id());
            if (!$parent->isShipSeparately())
                $retour = false;
        }

        //if bundle ship separately
        if ($product->getorder_item()->getproduct_type() == 'bundle') {
            if ($product->getorder_item()->isShipSeparately())
                $retour = false;
        }

        return $retour;
    }

    /**
     * Return product sub items (when ship together)
     *
     * @param unknown_type $product
     */
    public function getSubitems($product) {
        $retour = '';
        if (($product->getorder_item()->getproduct_type() == 'bundle') || ($product->getorder_item()->getproduct_type() == 'configurable')) {
            foreach ($this->getItemsToShip() as $item) {
                if ($item->getorder_item()->getparent_item_id() == $product->getorder_item()->getId()) {
                    $qty = $item->getorder_item()->getqty_ordered() / $product->getorder_item()->getqty_ordered();

                    $name = $item->getorder_item()->getname();
                    $name .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($item->getproduct_id());

                    $retour .= '&nbsp;&nbsp;<i>' . $qty . 'x ' . $name . '</i><br>';
                }
            }
        }
        return $retour;
    }

    /**
     * Return store name for current order
     *
     * @return unknown
     */
    public function getOrderStoreName() {
        if ($this->getCurrentOrder()) {
            $storeId = $this->getCurrentOrder()->getStoreId();
            if (is_null($storeId)) {
                return nl2br($this->getCurrentOrder()->getStoreName());
            }
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }

    /**
     * Return comments for current order
     *
     */
    public function getComments() {
        if ($this->_comments == null) {
            $this->_comments = mage::helper('Organizer')->getEntityCommentsSummary('order', $this->getCurrentOrder()->getId(), true);
        }
        return $this->_comments;
    }

    /**
     * Return a combo with all actions
     *
     */
    public function getActionsAsCombo($name, $onChange) {
        $retour = '<select name="' . $name . '" id="' . $name . '" onchange="' . $onChange . '">';
        $retour .= '<option value=""></option>';

        $retour .= '<optgroup label="' . $this->__('Current Order') . '">';
        $retour .= '<option value="ajax;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/PrintShippingLabel', array('order_id' => $this->getCurrentOrder()->getId())) . '">' . $this->__('Print shipping label') . '</option>';
        $retour .= '<option value="ajax;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/PrintDocuments', array('order_id' => $this->getCurrentOrder()->getId())) . '">' . $this->__('Print documents') . '</option>';
        $retour .= '<option value="download;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/DownloadDocuments', array('order_id' => $this->getCurrentOrder()->getId())) . '">' . $this->__('Download documents') . '</option>';
        $retour .= '<option value="download;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/DownloadShippingLabel', array('order_id' => $this->getCurrentOrder()->getId())) . '">' . $this->__('Download shipping label file') . '</option>';
        $retour .= '</optgroup>';

        $retour .= '<optgroup label="' . $this->__('Picking list') . '">';
        $retour .= '<option value="ajax;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/PrintPickingList') . '">' . $this->__('Print picking list') . '</option>';
        $retour .= '<option value="download;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/DownloadPickingList') . '">' . $this->__('Download picking list') . '</option>';
        $retour .= '</optgroup>';

        $retour .= '<optgroup label="' . $this->__('Shipping softwares') . '">';
        $collection = mage::getModel('Orderpreparation/CarrierTemplate')->getCollection();
        foreach ($collection as $item) {
            $retour .= '<option value="download;' . $this->getUrl('adminhtml/OrderPreparation_OnePagePreparation/DownloadCarrierExportFile', array('template_id' => $item->getId())) . '">' . $this->__('Download ') . $item->getct_name() . $this->__(' file') . '</option>';
        }
        $retour .= '<option value="redirect;' . $this->getUrl('adminhtml/OrderPreparation_CarrierTemplate/ImportTracking') . '">' . $this->__('Import Tracking') . '</option>';
        $retour .= '</optgroup>';

        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return carrier template
     *
     */
    public function getCarrierTemplate() {
        if ($this->_carrierTemplate == null)
            $this->_carrierTemplate = mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($this->getCurrentOrder());
        return $this->_carrierTemplate;
    }

    /**
     * Return js code to execute on commit button 
     *
     */
    public function getCommitJsAction() {
        $retour = 'commit(';

        //save data
        $retour .= 'true,';

        if (mage::getStoreConfig('orderpreparation/commit_button_actions/create_shipments_invoices') == 1)
            $retour .= 'true,';
        else
            $retour .= 'false,';

        if (mage::getStoreConfig('orderpreparation/commit_button_actions/print_documents') == 1)
            $retour .= 'true,';
        else
            $retour .= 'false,';

        if (mage::getStoreConfig('orderpreparation/commit_button_actions/download_documents') == 1)
            $retour .= 'true,';
        else
            $retour .= 'false,';

        if (mage::getStoreConfig('orderpreparation/commit_button_actions/print_shipping_label') == 1)
            $retour .= 'true,';
        else
            $retour .= 'false,';

        if (mage::getStoreConfig('orderpreparation/commit_button_actions/select_next_order') == 1)
            $retour .= 'true';
        else
            $retour .= 'false';

        $retour .= ');';
        return $retour;
    }

    //*********************************************************************************************************************
    //*********************************************************************************************************************
    // URL
    //*********************************************************************************************************************
    //*********************************************************************************************************************

    /**
     * return submit url
     *
     */
    public function getSaveUrl() {
        return $this->getUrl('*/*/Save');
    }

    /**
     *
     *
     */
    public function getPrintDocumentUrl() {
        return $this->getUrl('*/*/index', array('previous_order_id' => $this->getCurrentOrder()->getId()));
    }

    /**
     *
     *
     */
    public function getDownloadDocumentUrl() {
        return $this->getUrl('*/*/index', array('previous_order_id' => $this->getCurrentOrder()->getId()));
    }

    /**
     *
     *
     */
    public function getPrintShipmentUrl() {
        return $this->getUrl('*/*/index', array('previous_order_id' => $this->getCurrentOrder()->getId()));
    }

    /**
     * get next order url
     *
     */
    public function getNextOrderUrl() {
        return $this->getUrl('*/*/index', array('previous_order_id' => $this->getCurrentOrder()->getId()));
    }

    public function getRefreshPageUrl() {
        $previous_order_id = $this->getRequest()->getParam('previous_order_id');
        return $this->getUrl('*/*/index', array('previous_order_id' => $previous_order_id, 'confirm' => 1));
    }

    //*********************************************************************************************************************
    //*********************************************************************************************************************
    // Barcode & Location
    //*********************************************************************************************************************
    //*********************************************************************************************************************

    /**
     * Return product location in warehouse
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function getProductLocation($productId) {
        //init preparation warehouse
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($preparationWarehouseId);
        return $warehouse->getProductLocation($productId);
    }

    /**
     * return barcode for product
     * @param <type> $orderToPrepareItem
     * @return <type>
     */
    public function getBarcode($orderToPrepareItem) {
        if ($orderToPrepareItem->getorder_item()->getproduct_type() != 'configurable') {
            $productId = $orderToPrepareItem->getproduct_id();
            $barcode = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
            return $barcode;
        } else {
            foreach ($this->getItemsToShip() as $item) {
                if ($item->getorder_item()->getparent_item_id() == $orderToPrepareItem->getorder_item()->getId()) {
                    $productId = $item->getproduct_id();
                    $barcode = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
                    return $barcode;
                }
            }
        }
    }

    /**
     * Return qty to ship for product
     * @param <type> $orderToPrepareItem
     */
    public function getOrderedQty($orderToPrepareItem) {
        return $orderToPrepareItem->getqty();
    }

	/**
	 * Return true if the current order is the last one
	 */
	public function isLastOrder()
	{
		$count = $this->getOrderCollection()->getSize();
        $current = 0;
        $pos = 0;
        foreach ($this->getOrderCollection() as $item) {
            if ($item->getorder_id() == $this->getCurrentOrder()->getId()) {
                $current = $pos;
                break;
            }
            $pos++;
        }
        return (($current + 1) == $count);
	}

}
