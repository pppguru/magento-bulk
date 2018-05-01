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
class MDN_Purchase_Block_Product_Widget_StockDetails_Summary extends Mage_Adminhtml_Block_Template {

    private $_stocks = null;
    private $_supplyNeed = null;
    /**
     * Product get/set
     *
     * @var unknown_type
     */
    private $_product = null;
    public function setProduct($Product) {
        $this->_product = $Product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    public function getManualSupplyNeedQty() {
        $html = $this->getProduct()->getmanual_supply_need_qty();
        if ($html == '')
            $html = 0;
        return $html;
    }

    /**
     * Return waiting for delivery qty
     *
     */
    public function getWaitingForDeliveryQty() {
        return $this->getProduct()->getwaiting_for_delivery_qty();
    }

    /**
     * Return supply date
     *
     */
    public function getSupplyDate() {
        $supplyDate = '';
        if ($this->getProduct()->getsupply_date() != '') {
            $supplyDate = $this->formatDate($this->getProduct()->getsupply_date(), 'long');
        }
        return $supplyDate;
    }

    /**
     * Return stocks for product
     *
     * @return unknown
     */
    private function getStocks() {
        if ($this->_stocks == null) {
            $this->_stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($this->getProduct()->getId());
        }
        return $this->_stocks;
    }

    /**
     * Return purchase order for next delivery
     *
     */
    public function getNextPurchaseOrder() {
        $collection = mage::getModel('Purchase/OrderProduct')
            ->getCollection()
            ->addFieldToFilter('pop_product_id', $this->getProduct()->getId())
            ->join('Purchase/Order','po_num=pop_order_num AND po_status="'.MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY.'" ')
            ->join('Purchase/Supplier','po_sup_num=sup_id');
        $poLinks = array();
        foreach ($collection as $datas){
            $url = $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $datas->getpo_num()));
            $poLinks[] = '<a href="'.$url.'" target="_blanck">'.$datas->getpo_order_id().'</a> <i>('.$datas->getsup_name().')</i>';
        }
        return implode('<br/>',$poLinks);
    }

    public function getGeneralStatus() {
        return $this->__($this->getSupplyNeed()->getsn_status());
    }

    private function getSupplyNeed() {
        if ($this->_supplyNeed == null) {
            $this->_supplyNeed = mage::getResourceModel('Purchase/SupplyNeeds_NewCollection')->addFieldToFilter('entity_id' , $this->getProduct()->getId())->getFirstItem();
        }
        return $this->_supplyNeed;
    }

    public function getSupplyNeedMinQty() {
        $sn = $this->getSupplyNeed();
        return (int)$sn->getqty_min();
    }

    public function getSupplyNeedMaxQty() {
        $sn = $this->getSupplyNeed();
        return (int)$sn->getqty_max();
    }

}