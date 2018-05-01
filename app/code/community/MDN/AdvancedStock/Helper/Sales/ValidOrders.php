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
class MDN_AdvancedStock_Helper_Sales_ValidOrders extends Mage_Core_Helper_Abstract {

    const IS_NOT_VALID = 0;
    const IS_VALID = 1;
    const FORCE_IS_VALID = 2;
    const FORCE_IS_NOT_VALID = 3;

    /**
     * Add conditions to an order collection
     *
     * @param unknown_type $collection
     * @return unknown
     */
    public function addConditionToCollection($collection) {
        $collection->addFieldToFilter('is_valid', self::IS_VALID);

        return $collection;
    }

    /**
     * Check if an order is valid
     *
     * @param unknown_type $order
     * @return unknown
     */
    public function orderIsValid($order) {
        $isValidFlag =$order->getis_valid();
        if ( $isValidFlag== self::IS_VALID || $isValidFlag == self::FORCE_IS_VALID)
            return true;
        else
            return false;
    }

    /**
     * Update is_valid value for sales order
     *
     * @param unknown_type $order
     */
    public function updateIsValid(&$order, $save = false) {
        $isValid = true;
        $debug = '';
        $continue = true;

        $debug .= 'UpdateIsValid Order #'.$order->getIncrementId()."\n";

        //check customer group
        if ($customerId = $order->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customerGroup = $customer->getgroup_id();
            $forcedCustomerGroups = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/force_customer_group'));
            if (in_array($customerGroup, $forcedCustomerGroups)){
                $debug .= 'Order is valid because customer group is forced as valid'."\n";
                $continue = false;
            }
        }

        // -------------------- FORCE TO VALID --------------------

        //check shipping method
        if ($continue)
        {
            $shippingMethod = $order->getshipping_method();
            $forcedShippingMethods = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/force_shipping_method'));
            if (in_array($shippingMethod, $forcedShippingMethods)) {
                $debug .= 'Order is valid because shipping method is forced as valid'."\n";
                $continue = false;
            }
        }

        //check payment method
        if ($continue)
        {
            $payment = $order->getPayment();
            if($payment != null) {
                $paymentMethod = $payment->getMethodInstance()->getcode();
                $forcedPaymentMethods = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/force_payment_method'));
                if (in_array($paymentMethod, $forcedPaymentMethods)) {
                    $debug .= 'Order is valid because payment is forced as valid'."\n";
                    $continue = false;
                }
            }
        }

        // -------------------- FORCE TO NOT VALID --------------------


        //If Payment validated = 0
        if ($continue && mage::getStoreConfig('advancedstock/valid_orders/require_payment_validated')) {
            if ($order->getpayment_validated() == 0) {
                $isValid = false;
                $debug .= 'Order is not valid because payment is not validated'."\n";
            }
        }

        //If order method is defined as untrustable
        if ($continue)
        {
            $payment = $order->getPayment();
            if($payment != null) {
                $paymentMethod = $payment->getMethodInstance()->getcode();
                $forcedPaymentMethods = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/exclude_by_payment_method'));
                if (in_array($paymentMethod, $forcedPaymentMethods)) {
                    $isValid = false;
                    $debug .= 'Order is not valid because payment method is define as untrustable'."\n";
                }
            }
        }

        //If order status is set for exclusion
        $excludeStatuses = mage::getStoreConfig('advancedstock/valid_orders/exclude_status');
        if ($continue && ($excludeStatuses != '')) {
            $orderStatus = $order->getStatus();
            $t = explode(',', $excludeStatuses);
            if (in_array($orderStatus, $t)) {
                $isValid = false;
                $debug .= 'Order is not valid because status is excluded'."\n";
            }
        }

        //update value
        if ($isValid) {
            $order->setis_valid(self::IS_VALID);
            $debug .= 'set is valid'."\n";
        } else {
            $order->setis_valid(self::IS_NOT_VALID);
            $debug .= 'set is NOT valid'."\n";
        }

        //save
        if ($save) {
            $debug .= ' SAVING '."\n";
            $order->save();
        }

        mage::log($debug,null,'erp_order_validity_calculation.log');
    }

    /**
     * Apply "Valid order" rules for 1 order
     *
     * @param unknown_type $orderId
     */
    public function UpdateIsValidWithSave($orderId) {
        $order = mage::getModel('sales/order')->load($orderId);
        $this->updateIsValid($order, true);
    }


    /**
     * Force an order as Valid
     *
     * @param unknown_type $orderId
     */
    public function forceOrderAsValid($orderId) {
        $order = mage::getModel('sales/order')->load($orderId);
        $order->setis_valid(self::IS_VALID);
        $order->save();
    }

}