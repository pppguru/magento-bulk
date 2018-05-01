<?php

class MDN_ProductReturn_Helper_Tax extends Mage_Core_Helper_Abstract
{
    public function exclToIncl($productId, $price, $address)
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        $helper = Mage::getSingleton('tax/calculation');
        $request = $helper->getRateRequest($address, $address, false);
        $request->setProductClassId($product->getTaxClassId());
        $taxRate = $helper->getRate($request);

        $finalPrice = number_format($price * (1 + $taxRate / 100), 2, '.', '');

        return $finalPrice;
    }

    public function shippingInclToExcl($valueInclTax, $order)
    {
        $shippingTaxClassId = Mage::getStoreConfig('tax/classes/shipping_tax_class',$order->getStoreId());
        $customerGroupId = $order->getCustomerGroupId();
        $group = Mage::getModel('customer/group')->load($customerGroupId);
        $custTaxClassId = $group->getTaxClassId();
        $store = Mage::getModel('core/store')->load($order->getStoreId());
        $taxCalculationModel = Mage::getSingleton('tax/calculation');
        $request = $taxCalculationModel->getRateRequest($order->getShippingAddress(), $order->getBillingAddress(), $custTaxClassId, $store);
        $shippingTaxPercent = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClassId));

        $value = ($valueInclTax / (1 + $shippingTaxPercent / 100));
        return (number_format($value, 4, '.', ''));
    }

}