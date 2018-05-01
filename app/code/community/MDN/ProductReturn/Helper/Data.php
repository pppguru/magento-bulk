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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if embedded ERP is installed
     *
     */
    public function erpIsInstalled()
    {
        return (mage::getStoreConfig('advancedstock/erp/is_installed') == 1);
    }

    /**
     * Create Rma reference
     *
     * @param unknown_type $rma
     *
     * @return string
     */
    public function createRmaReference($rma)
    {
        $retour = $rma->getSalesOrder()->getIncrementId();
        $retour .= '-';

        //parse all rma for this order to define id
        $collection = mage::getModel('ProductReturn/Rma')
            ->getCollection()
            ->addFieldToFilter('rma_order_id', $rma->getSalesOrder()->getId());
        $retour .= ($collection->getSize() + 1);

        return $retour;
    }


    /**
     * Check if it is possible to request for product return on order depending of the date of the last shipment
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return unknown
     */
    public function IsOrderAvailable($order)
    {
        /** @var  $order Mage_Sales_Model_Order */
        //retrieve limit date
        $date_limite = null;
        foreach ($order->getShipmentsCollection() as $_shipment) {
            if ($date_limite < $_shipment->getupdated_at())
                $date_limite = $_shipment->getupdated_at();
        }

        //return false if order has no shipment
        if ($date_limite == null)
            return $this->__('Your order was never shipped');

        $daysAvailable = Mage::getStoreConfig('productreturn/product_return/max_days_request');
        if (!$daysAvailable)
            $daysAvailable = 30;
        $date_limite   = date_create($date_limite . "+ " . $daysAvailable . " days");
        $now           = date_create();
        if ($date_limite->format('Ymd') > $now->format('Ymd')) {

            $allinrma     = array();
            $itemsinorder = $order->getItemsCollection();
            foreach ($itemsinorder as $iteminorder):
                $allinrma[$iteminorder->getProductId()] = $iteminorder->getQtyShipped();
                $allinrma[$iteminorder->getProductId()] -= $this->getIsalreadyInRma($iteminorder);
            endforeach;

            if (array_sum($allinrma) <= 0) {
                return $this->__('No products available');
            }

            return true;
        } else {
            return $this->__('Ability to return products from this order expired on %s', $date_limite->format('Y/m/d'));
        }


    }

    /**
     * Return rma for generated order
     *
     * @param unknown_type $generatedOrder
     *
     * @return \Mage_Core_Model_Abstract|null
     */
    public function getRmaFromGeneratedOrder($generatedOrder)
    {
        $rma = null;

        //if this is not an order generated from a rma
        if ($generatedOrder->getorder_type() != 'rma')
            return $rma;

        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->addFieldToFilter('rp_object_type', 'order')
            ->addFieldToFilter('rp_object_id', $generatedOrder->getId());
        foreach ($collection as $rmaProduct) {
            $rma = mage::getModel('ProductReturn/Rma')->load($rmaProduct->getrp_rma_id());
            break;
        }

        return $rma;
    }

    /**
     * 
     *
     */
    public function getIsalreadyInRma($item)
    {

        $qty = 0;

        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->addFieldToFilter('rp_product_id', $item->getProductId())
            ->addFieldToFilter('rp_orderitem_id', $item->getItemId());

        foreach ($collection as $item):
            $qty += $item->getrp_qty();
        endforeach;

        return $qty;

    }

    /**
     * Return product image
     *
     */
    public function getProductImage($pId, $size = '50')
    {
        $pload = mage::getModel('catalog/product')->load($pId);

        $pload = mage::helper('ProductReturn/Configurable')->getPublicConfigurableProduct($pload);

        if ($pload->getSmallImage() && ($pload->getSmallImage() != 'no_selection')) {
            try
            {
                return Mage::helper('catalog/image')->init($pload, 'small_image')->resize($size);
            }
            catch(Exception $ex)
            {
                //nothing
            }
        } else return '';

    }

    /**
     * Return product sku
     *
     */
    public function getProductSku($pId)
    {
        $pload = mage::getModel('catalog/product')->load($pId);

        return $pload->getSku();
    }

    public function log($msg)
    {
        mage::log($msg, null, 'product_return.log');
    }

}