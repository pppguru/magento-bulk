<?php

class MDN_Purchase_Helper_Supplier extends Mage_Core_Helper_Abstract {

    /**
     * Creates suppliers from manufacturers
     */
    public function synchronizeManufacturersAndSuppliers() {
        
        //get manufacturers
        $manufacturerAttribute = Mage::getStoreConfig('purchase/manufacturer_supplier_synchronization/manufacturer_attribute');
        if (!$manufacturerAttribute)
            throw new Exception('Manufacturer attribute is not configured');
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($product->getResource()->getTypeId())
                        ->addFieldToFilter('attribute_code', $manufacturerAttribute)
                        ->load(false);
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        //parse manufacturer
        foreach ($manufacturers as $manufacturer) {
            $code = $manufacturer['value'];
            $name = $manufacturer['label'];

            //create supplier from manufacturer
            $supplier = $this->createSupplierFromManufacturer($code, $name);

            //link manufacturer's products to supplier
            $productIds = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect($manufacturerAttribute)
                            ->addAttributeToFilter($manufacturerAttribute, $code)
                            ->getAllIds();
            foreach ($productIds as $productId) {
                $this->linkProductToSupplier($productId, $supplier->getId());
            }
        }
    }

    /**
     * Create supplier from manufacturer
     */
    public function createSupplierFromManufacturer($manufacturerCode, $manufacturerName)
    {
        $supplier = Mage::getModel('Purchase/Supplier')->load($manufacturerCode, 'sup_code');
        if (!$supplier->getId())
            $supplier->setsup_name($manufacturerName)->setsup_code($manufacturerCode)->save();
        return $supplier;
    }

    /**
     * Return true if a product is alread linked to a supplier
     * @param <type> $productId
     * @param <type> $supplierId
     * @return <type>
     */
    public function isProductLinkedToSupplier($productId, $supplierId) {
        $pps = Mage::getModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->addFieldToFilter('pps_product_id', $productId)
                        ->addFieldToFilter('pps_supplier_num', $supplierId);
        $isLinked = (count($pps) > 0);
        return $isLinked;
    }

    /**
     * Link a product to a supplier
     * @param <type> $productId
     * @param <type> $supplierId
     */
    public function linkProductToSupplier($productId, $supplierId) {
        //check that association doesnt exists yet
        if (!$this->isProductLinkedToSupplier($productId, $supplierId)) {
            $obj = Mage::getModel('Purchase/ProductSupplier');
            $obj->setpps_product_id($productId);
            $obj->setpps_supplier_num($supplierId);
            $obj->save();
        }
    }

    /**
     *
     * @param <type> $productId
     * @param <type> $manufacturerCode
     */
    public function removeProductManufacturerAssociation($productId, $manufacturerCode)
    {
        //get supplier
        $supplier = Mage::getModel('Purchase/Supplier')->load($manufacturerCode, 'sup_code');
        if ($supplier->getId())
        {
            $pps = Mage::getModel('Purchase/ProductSupplier')
                            ->getCollection()
                            ->addFieldToFilter('pps_product_id', $productId)
                            ->addFieldToFilter('pps_supplier_num', $supplier->getId())
                            ->getFirstItem();
            if ($pps && $pps->getId())
                    $pps->delete();

        }
    }
}
