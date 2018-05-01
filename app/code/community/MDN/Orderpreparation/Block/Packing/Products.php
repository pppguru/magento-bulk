<?php


class MDN_OrderPreparation_Block_Packing_Products extends Mage_Core_Block_Template {

    public function getProducts($groupId = null)
    {
        $orderId = $this->getOrder()->getId();
        $products = Mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($orderId);
        $groupProducts = array();
        foreach($products as $product)
        {
            $orderItem = Mage::getModel('sales/order_item')->load($product['order_item_id']);
            $orderItemGroup = $orderItem->getparent_item_id();
            if (!$orderItemGroup)
                $orderItemGroup = 'simple';
            
            if ($orderItemGroup == $groupId)
                $groupProducts[] = $product;
        }
        
        return $groupProducts;
    }
    
    /**
     * Return groups
     */
    public function getGroups()
    {
        $groups = array();

        $orderId = $this->getOrder()->getId();
        $products = Mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($orderId);
        foreach($products as $product)
        {
            if (!$this->productManageStock($product))
                continue;
            
            $orderItem = Mage::getModel('sales/order_item')->load($product['order_item_id']);
            if ($orderItem->getparent_item_id() == null)
            {
                if (!isset($groups['simple']))
                    $groups['simple'] = 'Simple products';
            }
            else
            {
                $parentOrderItem = Mage::getModel('sales/order_item')->load($orderItem->getparent_item_id());
                $groups[$orderItem->getparent_item_id()] = $parentOrderItem->getName();
            }
        }
        
        return $groups;
    }

    public function getProductImageUrl($orderToPrepareItem)
    {
        $productId = $orderToPrepareItem->getproduct_id();
        $product = Mage::getModel('catalog/product')->load($productId);

        if (($product->getsmall_image()) && ($product->getsmall_image() != 'no_selection')) {
            return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
        } else {
            //try to find image from configurable product
            $configurableProduct = Mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getConfigurableProduct($product);
            if ($configurableProduct) {
                if (($configurableProduct->getsmall_image()) && ($configurableProduct->getsmall_image() != 'no_selection')) {
                    return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $configurableProduct->getSmallImage();
                }
            }
        }

        return '';

    }

    /**
     * return true if product manage stocks
     * 
     * @param type $orderToPrepareItem
     * @return type 
     */
    public function productManageStock($orderToPrepareItem)
    {
        $productId = $orderToPrepareItem->getproduct_id();
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getStockItem()->getManageStock();
    }
    
    /**
     * return true if user must confirm weight 
     */
    public function askForWeight()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/ask_for_weight')  ? '1' : '0');
    }
        
    /**
     * return true if user must confirm the parcel count 
     */
    public function askForParcelCount()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/ask_for_parcel_count')  ? '1' : '0');
    }
    
    
    /**
     * Return 1 if the user must process groups one by one
     */
    public function displayOnlyCurrentGroup()
    {
        return (int) Mage::getStoreConfig('orderpreparation/packing/display_current_group_only');
    }
    
    /**
     * return weight 
     */
    public function getWeight()
    {
        $orderId = $this->getOrder()->getId();
        $orderToPrepare = Mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
        return $orderToPrepare->getreal_weight();
        
    }
    
    /**
     * return if we must Display + / - buttons
     */
    public function displayQuantityButtons()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/display_quantity_button')  ? '1' : '0');
    }
    
    /**
     * Return if we must display serials textarea
     * @return type
     */
    public function displaySerials()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/display_serials')  ? '1' : '0');
    }

    /**
     * Return if we must display barcode columns
     * @return type
     */
    public function displayBarcodes()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/display_barcode')  ? '1' : '0');
    }

    /**
     * Return teh number of column to display in the packing screen
     * @return type
     */
    public function getNumberOfColumns()
    {
       $columnCount = 6;

       if($this->displaySerials()){
         $columnCount++;
       }

       if($this->displayBarcodes()){
         $columnCount++;
       }
       
       return $columnCount;
    }

    /**
     * Return the image size displayed in packing step
     * prevent to display to big image when the setting is not defined or badly defined
     * 
     * @return int
     */
    public function getImageSize()
    {
      $defaultImageSize = 80;
      $maxImageSize = 600;
      $confImageSize = Mage::getStoreConfig('orderpreparation/packing/image_thumbnail_size');
      if($confImageSize){
        if($confImageSize < $maxImageSize){
          $defaultImageSize = $confImageSize;
        }else{
          $defaultImageSize = $maxImageSize;
        }
      }
      return $defaultImageSize;
    }

    /**
     * Return all available shipping methods
     */
    public function getShippingMethods()
    {
        $methods = mage::helper('Orderpreparation/ShippingMethods')->getArray();
        return $methods;
    }

    /**
     * Return html block with controls for carrier template fields
     */
    public function getCarrierTemplateFormHtml()
    {
        $html = '';
        $orderToPrepare = Mage::getModel('Orderpreparation/ordertoprepare')->load($this->getOrder()->getId(), 'order_id');
        $carrierTemplate = mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($orderToPrepare);
        if ($carrierTemplate != null)
        {
            $html .= $this->__('Fields for template "%s"', $carrierTemplate->getct_name());
            $html .= $carrierTemplate->getForm($orderToPrepare, 'carriertemplatedata');
        }

        return $html;
    }
}