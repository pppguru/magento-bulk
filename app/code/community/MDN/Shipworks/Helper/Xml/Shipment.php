<?php

class MDN_Shipworks_Helper_Xml_Shipment extends MDN_Shipworks_Helper_Xml {

    
    /**
     * 
     * @param type $order
     */
    public function WriteShipment($shipment) {
        $this->writeStartTag("Order");

        $order = $shipment->getOrder();
        
        $incrementId = $shipment->getIncrementId();
        $orderPostfix = '';
        $parts = preg_split('[\-]', $incrementId, 2);

        if (count($parts) == 2) {
            $incrementId = $parts[0];
            $orderPostfix = $parts[1];
        }

        $this->writeElement("OrderNumber", $incrementId);
        $this->writeElement("OrderDate", Mage::helper('Shipworks')->FormatDate($shipment->getCreatedAt()));
        $this->writeElement("LastModified", Mage::helper('Shipworks')->FormatDate($shipment->getUpdatedAt()));
        $this->writeElement("ShippingMethod", $order->getShippingDescription());
        $this->writeElement("StatusCode", $shipment->getStatus());
        $this->writeElement("CustomerID", $order->getCustomerId());

        // check for order-level gift messages
        $this->writeStartTag("Notes");
        $this->writeFullElement("Note", "Order #".$order->getincrement_id(), array("public" => "true"));
        if ($order->getGiftMessageId()) {
            $message = Mage::helper('giftmessage/message')->getGiftMessage($order->getGiftMessageId());
            $messageString = "Gift message for " . $message['recipient'] . ": " . $message['message'];

            $this->writeFullElement("Note", $messageString, array("public" => "true"));
        }
        $this->writeCloseTag("Notes");

        $address = $order->getBillingAddress();
        $this->writeStartTag("BillingAddress");
        $this->writeElement("FullName", $address->getName());
        $this->writeElement("Company", $address->getCompany());
        $this->writeElement("Street1", $address->getStreet(1));
        $this->writeElement("Street2", $address->getStreet(2));
        $this->writeElement("Street3", $address->getStreet(3));
        $this->writeElement("City", $address->getCity());
        $this->writeElement("State", $address->getRegionCode());
        $this->writeElement("PostalCode", $address->getPostcode());
        $this->writeElement("Country", $address->getCountryId());
        $this->writeElement("Phone", $address->getTelephone());
        $this->writeElement("Email", $order->getCustomerEmail());
        $this->writeCloseTag("BillingAddress");


        $billFullName = $address->getName();
        $billStreet1 = $address->getStreet(1);
        $billCity = $address->getCity();
        $billZip = $address->getPostcode();

        $address = $order->getShippingAddress();
        if (!$address) {
            // sometimes the shipping address isn't specified, so use billing
            $address = $order->getBillingAddress();
        }

        $this->writeStartTag("ShippingAddress");
        $this->writeElement("FullName", $address->getName());
        $this->writeElement("Company", $address->getCompany());
        $this->writeElement("Street1", $address->getStreet(1));
        $this->writeElement("Street2", $address->getStreet(2));
        $this->writeElement("Street3", $address->getStreet(3));
        $this->writeElement("City", $address->getCity());
        $this->writeElement("State", $address->getRegionCode());
        $this->writeElement("PostalCode", $address->getPostcode());
        $this->writeElement("Country", $address->getCountryId());
        $this->writeElement("Phone", $address->getTelephone());

        // if the addressses appear to be the same, use customer email as shipping email too
        if ($address->getName() == $billFullName &&
                $address->getStreet(1) == $billStreet1 &&
                $address->getCity() == $billCity &&
                $address->getPostcode() == $billZip) {
            $this->writeElement("Email", $order->getCustomerEmail());
        }

        $this->writeCloseTag("ShippingAddress");


        $payment = $order->getPayment();

        // CC info
        if (false) {
            $cc_num = $payment->getCcNumber();
        } else {
            $cc_num = $payment->getCcLast4();
            if (!empty($cc_num)) {
                $cc_num = '************' . $payment->getCcLast4();
            }
        }
        $cc_year = sprintf('%02u%s', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));


        $this->writeStartTag("Payment");
        $this->writeElement("Method", Mage::helper('payment')->getMethodInstance($payment->getMethod())->getTitle());

        $this->writeStartTag("CreditCard");
        $this->writeElement("Type", $payment->getCcType());
        $this->writeElement("Owner", $payment->getCcOwner());
        $this->writeElement("Number", $cc_num);
        $this->writeElement("Expires", $cc_year);
        $this->writeCloseTag("CreditCard");

        $this->writeCloseTag("Payment");

        $this->WriteOrderItems($shipment->getAllItems());

        $this->WriteOrderTotals($shipment->getOrder());

        $this->writeStartTag("Debug");
        $this->writeElement("OrderID", $shipment->getEntityId());
        $this->writeElement("OrderNumberPostfix", $orderPostfix);
        $this->writeCloseTag("Debug");

        $this->writeCloseTag("Order");
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $class
     * @param type $impact
     */
    protected function WriteOrderTotal($name, $value, $class, $impact = "add") {
        if ($value > 0) {
            $this->writeFullElement("Total", $value, array("name" => $name, "class" => $class, "impact" => $impact));
        }
    }

    /**
     * 
     * @param type $order
     */
    protected function WriteOrderTotals($order) {
        $this->writeStartTag("Totals");

        $this->WriteOrderTotal("Order Subtotal", $order->getSubtotal(), "ot_subtotal", "none");
        $this->WriteOrderTotal("Shipping and Handling", $order->getShippingAmount(), "shipping", "add");

        if ($order->getTaxAmount() > 0) {
            $this->WriteOrderTotal("Tax", $order->getTaxAmount(), "tax", "add");
        }

        // Magento 1.4 started storing discounts as negative values
        if (Mage::helper('Shipworks')->MagentoVersionGreaterOrEqualTo('1.4.0') && $order->getDiscountAmount() < 0) {
            $couponcode = $order->getCouponCode();
            $this->WriteOrderTotal("Discount ($couponcode)", -1 * $order->getDiscountAmount(), "discount", "subtract");
        }

        if (!Mage::helper('Shipworks')->MagentoVersionGreaterOrEqualTo('1.4.0') && $order->getDiscountAmount() > 0) {
            $couponcode = $order->getCouponCode();
            $this->WriteOrderTotal("Discount ($couponcode)", $order->getDiscountAmount(), "discount", "subtract");
        }

        if ($order->getGiftcertAmount() > 0) {
            $this->WriteOrderTotal("Gift Certificate", $order->getGiftcertAmount(), "giftcertificate", "subtract");
        }

        if ($order->getAdjustmentPositive()) {
            $this->WriteOrderTotal("Adjustment Refund", $order->getAdjustmentPositive(), "refund", "subtract");
        }

        if ($order->getAdjustmentNegative()) {
            $this->WriteOrderTotal("Adjustment Fee", $order->getAdjustmentPositive(), "fee", "add");
        }

        $this->WriteOrderTotal("Grand Total", $order->getGrandTotal(), "total", "none");

        $this->writeCloseTag("Totals");
    }

    
    /**
     * 
     * @param Mage_Sales_Model_Order_Item $item
     * @return int
     */
    protected function getCalculationPrice($item) {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            if (Mage::helper('Shipworks')->MagentoVersionGreaterOrEqualTo('1.3.0')) {
                return $item->getPrice();
            } else {
                if ($item->hasCustomPrice()) {
                    return $item->getCustomPrice();
                } else if ($item->hasOriginalPrice()) {
                    return $item->getOriginalPrice();
                }
            }
        }

        return 0;
    }

    /**
     * 
     * @param type $orderItems
     */
    protected function WriteOrderItems($orderItems) {
        $this->writeStartTag("Items");

        $parentMap = Array();

        // go through each item in the collection
        foreach ($orderItems as $item) {

            $shipmentItem = $item;
            $item = $item->getOrderItem();
            
            // keep track of item Id and types
            $parentMap[$item->getItemId()] = $item->getProductType();

            // get the sku
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $sku = $item->getProductOptionByCode('simple_sku');
            } else {
                $sku = $item->getSku();
            }

            // weights are handled differently if the item is a bundle or part of a bundle
            $weight = $item->getWeight();
            if ($item->getIsVirtual()) {
                $weight = 0;
            }

            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $name = $item->getName() . " (bundle)";
                $unitPrice = $this->getCalculationPrice($item);
            } else {
                $name = $item->getName();

                // if it's part of a bundle
                if (is_null($item->getParentItemId())) {
                    $unitPrice = $this->getCalculationPrice($item);
                } else {
                    // need to see if the parent is a bundle or not
                    $isBundle = ($parentMap[$item->getParentItemId()] == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
                    if ($isBundle) {
                        // it's a bundle member - price and weight come from the bundle definition itself
                        $unitPrice = 0;
                        $weight = 0;
                    } else {
                        // don't even want to include if the parent item is anything but a bundle
                        continue;
                    }
                }
            }

            // Magento 1.4+ has Cost
            $unitCost = 0;
            if (Mage::helper('Shipworks')->MagentoVersionGreaterOrEqualTo('1.4.0') && $item->getBaseCost() > 0) {
                $unitCost = $item->getBaseCost();
            } else if (Mage::helper('Shipworks')->MagentoVersionGreaterOrEqualTo('1.3.0')) {
                // Magento 1.3 didn't seem to copy Cost to the item from the product
                // fallback to the Cost defined on the product.

                $product = Mage::getModel('catalog/product');
                $productId = $item->getProductId();
                $product->load($productId);

                if ($product->getCost() > 0) {
                    $unitCost = $product->getCost();
                }
            }

            $this->writeStartTag("Item");

            $this->writeElement("ItemID", $item->getItemId());
            $this->writeElement("ProductID", $item->getProductId());
            $this->writeElement("Code", $sku);
            $this->writeElement("SKU", $sku);
            $this->writeElement("Name", $name);
            $this->writeElement("Quantity", (int) $shipmentItem->getQty());
            $this->writeElement("UnitPrice", $unitPrice);
            $this->writeElement("UnitCost", $unitCost);

            if (!$weight) {
                $weight = 0;
            }
            $this->writeElement("Weight", $weight);


            $this->writeStartTag("Attributes");
            $opt = $item->getProductOptions();
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                if (is_array($opt) &&
                        isset($opt['attributes_info']) &&
                        is_array($opt['attributes_info']) &&
                        is_array($opt['info_buyRequest']) &&
                        is_array($opt['info_buyRequest']['super_attribute'])) {
                    $attr_id = $opt['info_buyRequest']['super_attribute'];
                    reset($attr_id);
                    foreach ($opt['attributes_info'] as $sub) {
                        $this->writeStartTag("Attribute");
                        $this->writeElement("Name", $sub['label']);
                        $this->writeElement("Value", $sub['value']);
                        $this->writeCloseTag("Attribute");

                        next($attr_id);
                    }
                }
            }

            if (is_array($opt) &&
                    isset($opt['options']) &&
                    is_array($opt['options'])) {
                foreach ($opt['options'] as $sub) {
                    $this->writeStartTag("Attribute");
                    $this->writeElement("Name", $sub['label']);
                    $this->writeElement("Value", $sub['value']);
                    $this->writeCloseTag("Attribute");
                }
            }

            // Order-item level Gift Messages are created as item attributes in ShipWorks
            if ($item->getGiftMessageId()) {
                $message = Mage::helper('giftmessage/message')->getGiftMessage($item->getGiftMessageId());

                // write the gift message as an attribute
                $this->writeStartTag("Attribute");
                $this->writeElement("Name", "Gift Message");
                $this->writeelement("Value", $message['message']);
                $this->writeCloseTag("Attribute");

                // write the gift messgae recipient as an attribute
                $this->writeStartTag("Attribute");
                $this->writeElement("Name", "Gift Message, Recipient");
                $this->writeelement("Value", $message['recipient']);
                $this->writeCloseTag("Attribute");
            }


            // Uncomment the following lines to include a custom product attribute in the downloaded data.
            // These will appear as Order Item Attributes in ShipWorks.
            //$product = Mage::getModel('catalog/product');
            //$productId = $product->getIdBySku($sku);
            //$product->load($productId);
            //$value = $product->getAttributeText("attribute_code_here");
            //if ($value)
            //{
            //     // write the gift message as an attribute
            //     writeStartTag("Attribute");
            //     writeElement("Name", "Attribute_title_here");
            //     writeelement("Value", $value);
            //     writeCloseTag("Attribute");
            //}

            $this->writeCloseTag("Attributes");

            $this->writeCloseTag("Item");
        }

        $this->writeCloseTag("Items");
    }

}