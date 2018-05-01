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
class MDN_Purchase_Model_ErpObserver {

    /**
     * Add custom tabs to erp product sheet
     *
     */
    public function advancedstock_product_edit_create_tabs(Varien_Event_Observer $observer) {

        //init vars
        $tab = $observer->getEvent()->gettab();
        $product = $observer->getEvent()->getproduct();
        $layout = $observer->getEvent()->getlayout();


        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/purchase_settings')) {
            $tab->addTab('tab_purchase_settings', array(
                'label' => Mage::helper('purchase')->__('Purchase Settings'),
                'content' => $layout->createBlock('Purchase/Product_Edit_Tabs_Settings')
                        ->setTemplate('Purchase/Product/Edit/Tab/Settings.phtml')
                        ->setProduct($product)
                        ->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/purchase_orders')) {
            $tab->addTab('tab_po', array(
                'label' => Mage::helper('purchase')->__('Purchase Orders'),
                'content' => $layout->createBlock('Purchase/Product_Edit_Tabs_AssociatedOrdersGrid')->setProduct($product)->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/suppliers')) {
            $tab->addTab('tab_suppliers', array(
                'label' => Mage::helper('purchase')->__('Suppliers'),
                'content' => $layout
                        ->createBlock('Purchase/Product_Edit_Tabs_AssociatedSuppliers')
                        ->setShowForm(true)
                        ->setTemplate('Purchase/Product/Edit/Tab/AssociatedSuppliers.phtml')
                        ->setProduct($product)
                        ->toHtml(),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/packaging')) {
            if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
                $tab->addTab('tab_packaging', array(
                    'label' => Mage::helper('purchase')->__('Packaging'),
                    'content' => $layout
                            ->createBlock('Purchase/Product_Edit_Tabs_Packaging')
                            ->setTemplate('Purchase/Product/Edit/Tab/Packaging.phtml')
                            ->setProduct($product)
                            ->toHtml(),
                ));
            }
        }
    }

    /**
     * Save custom data from erp product sheet
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_sheet_save(Varien_Event_Observer $observer) {


        //init vars
        $data = $observer->getEvent()->getpost_data();
        $product = $observer->getEvent()->getproduct();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/purchase_settings')) {
            //save custom data
            $purchaseData = $data['purchase'];

            if (!isset($purchaseData['exclude_from_supply_needs']))
                $purchaseData['exclude_from_supply_needs'] = '0';


            if (!isset($purchaseData['default_supply_delay']) || ($purchaseData['default_supply_delay'] == ''))
                $purchaseData['default_supply_delay'] = new Zend_Db_Expr('null');

            $needUpdate = false;

            if($product->getexclude_from_supply_needs() != $purchaseData['exclude_from_supply_needs']){
                $product->setexclude_from_supply_needs($purchaseData['exclude_from_supply_needs']);
                $needUpdate = true;
            }

            if($product->getdefault_supply_delay() != $purchaseData['default_supply_delay']){
                $product->setdefault_supply_delay($purchaseData['default_supply_delay']);
                $needUpdate = true;
            }

            if($product->getpurchase_tax_rate() != $purchaseData['purchase_tax_rate']){
                $product->setpurchase_tax_rate($purchaseData['purchase_tax_rate']);
                $needUpdate = true;
            }

            if($product->getmanual_supply_need_date() != $purchaseData['manual_supply_need_date']){
                $product->setmanual_supply_need_date($purchaseData['manual_supply_need_date']);
                $needUpdate = true;
            }

            if($product->getmanual_supply_need_qty() != $purchaseData['manual_supply_need_qty']){
                $product->setmanual_supply_need_qty($purchaseData['manual_supply_need_qty']);
                $needUpdate = true;
            }

            if($product->getmanual_supply_need_comments() != $purchaseData['manual_supply_need_comments']){
                $product->setmanual_supply_need_comments($purchaseData['manual_supply_need_comments']);
                $needUpdate = true;
            }

            //save only if out of stock period has changed
            if($needUpdate)
                $product->save();
        }

        //******************************
        //save packaging data
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/packaging')) {
            if (mage::helper('purchase/Product_Packaging')->isEnabled()) {

                //save existing packages
                $packagingData = $data['packaging'];
                $packagings = mage::helper('purchase/Product_Packaging')->getPackagingForProduct($product->getId());
                foreach ($packagings as $packaging) {
                    $id = $packaging->getId();

                    if (!isset($packagingData[$id]['delete']) || !$packagingData[$id]['delete']) {
                        //force radio values
                        if (!isset($packagingData[$id]['pp_is_default_sales']))
                            $packagingData[$id]['pp_is_default_sales'] = 0;
                        if (!isset($packagingData[$id]['pp_is_default']))
                            $packagingData[$id]['pp_is_default'] = 0;

                        foreach ($packagingData[$id] as $key => $value)
                            $packaging->setData($key, $value);
                        $packaging->save();
                    }
                    else
                        $packaging->delete();
                }


                //add new packaging
                if ($packagingData['new']['pp_name'] != '') {
                    $newPackaging = mage::getModel('Purchase/Packaging')
                            ->setpp_product_id($product->getId())
                            ->setpp_name($packagingData['new']['pp_name'])
                            ->setpp_qty($packagingData['new']['pp_qty'])
                            ->setpp_is_default(isset($packagingData['new']['pp_is_default']) ? ($packagingData['new']['pp_is_default']) : 0)
                            ->setpp_is_default_sales((isset($packagingData['new']['pp_is_default_sales']) ? $packagingData['new']['pp_is_default_sales'] : 0))
                            ->save();
                }
            } //endif product packaging enabled
        }
    }

    /**
     * Event raised after catalog_product saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalog_product_save_after(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getproduct();
        $productId = $product->getId();

        //check if manufacturer as changed
        if (Mage::getStoreConfig('purchase/manufacturer_supplier_synchronization/auto_sync')) {
            $manufacturerAttributeName = Mage::getStoreConfig('purchase/manufacturer_supplier_synchronization/manufacturer_attribute');
            if ($manufacturerAttributeName) {
                $manufacturerBefore = $product->getOrigData($manufacturerAttributeName);
                $manufacturerAfter = $product->getData($manufacturerAttributeName);
                if ($manufacturerBefore != $manufacturerAfter) {
                    $manufacturerCode = $manufacturerAfter;
                    $manufacturerName = $product->getAttributeText($manufacturerAttributeName);

                    //remove product from "old" supplier
                    $helper = Mage::helper('purchase/Supplier');
                    if ($manufacturerBefore) {
                        $helper->removeProductManufacturerAssociation($productId, $manufacturerBefore);
                    }

                    //link product to supplier
                    if ($manufacturerName) {
                        $supplier = $helper->createSupplierFromManufacturer($manufacturerCode, $manufacturerName);
                        $helper->linkProductToSupplier($productId, $supplier->getId());
                    }
                }
            }
        }
    }

    /**
     * Waiting for delivery qty has changed for product
     * */
    public function product_waiting_for_delivery_qty_change(Varien_Event_Observer $observer) {
        $productId = $observer->getEvent()->getproduct_id();
    }

    /**
     * Event raised when stock changes
     * Check if we have to update supply needs
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_stock_aftersave(Varien_Event_Observer $observer) {
        $stock = $observer->getEvent()->getstock();

        //update product cost if qty change
        if ($this->objectDataHasChanged($stock, 'qty')) {
            $productId = $stock->getproduct_id();
                mage::helper('BackgroundTask')->AddTask('Update cost for product #' . $productId,
                    'purchase/Product',
                    'updateProductCost',
                    $productId,
                    null,
                    true,
                    5
            );
        }
    }

    /**
     * Return true if data object changed (compare data and origdata)
     *
     * @param unknown_type $object
     * @param unknown_type $dataName
     */
    protected function objectDataHasChanged($object, $dataName) {
        $origValue = $object->getOrigData($dataName);
        $currentValue = $object->getData($dataName);

        return ($origValue != $currentValue);
    }


    /**
     * Handle to recalculate product waiting for delivery qty and date for product
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_force_stocks_update_requested(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getproduct();

        mage::helper('purchase/Product')->updateProductWaitingForDeliveryQty($product->getId());
        mage::helper('purchase/Product')->updateProductDeliveryDate($product->getId());
    }

    /**
     * Add supplier and supplier reference columns in erp > products grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_grid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/productlist_columns/suppliers')) {
            $grid->addColumn('suppliers', array(
                'header' => Mage::helper('purchase')->__('Suppliers'),
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_Suppliers',
                'filter' => 'Purchase/Widget_Column_Filter_ProductSupplier',
                'index' => 'entity_id'
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/productlist_columns/supplier_skus')) {
            $grid->addColumn('suppliers_sku', array(
                'header' => Mage::helper('purchase')->__('Suppliers Sku'),
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliersSku',
                'filter' => 'Purchase/Widget_Column_Filter_ProductSupplierSku',
                'index' => 'entity_id'
            ));
        }
    }

    /**
     *  Add supplier & sku columns in mass stock editor
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_masstockeditor_grid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('suppliers', array(
            'header' => Mage::helper('purchase')->__('Suppliers'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliers',
            'filter' => 'Purchase/Widget_Column_Filter_ProductSupplier',
            'index' => 'product_id',
            'sortable' => false
        ));

        $grid->addColumn('suppliers_sku', array(
            'header' => Mage::helper('purchase')->__('Suppliers Sku'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliersSku',
            'filter' => 'Purchase/Widget_Column_Filter_ProductSupplierSku',
            'index' => 'product_id',
            'sortable' => false
        ));
    }


    /**
     * Add massaction "link to supplier" to ERP product grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_grid_preparemassaction(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();
        $grid->setMassactionIdField('entity_id');
        $grid->getMassactionBlock()->setFormFieldName('ProductsList');

        $helper = Mage::helper('purchase');

        $grid->getMassactionBlock()->addItem('add_pps', array(
            'label' => $helper->__('Link to a supplier'),
            'url' => mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Products/MassAssociateToSupplier/supplier_id/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'suppliers',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Supplier'),
                    'values' => $this->getSupplierMenuForMassAction()
                )
            )
        ));

        $grid->getMassactionBlock()->addItem('del_pps', array(
            'label' => $helper->__('Unlink with a supplier'),
            'url' => mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Products/MassRemoveAssociationWithSupplier/supplier_id/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'suppliers',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Supplier'),
                    'values' => $this->getSupplierMenuForMassAction()
                )
            )
        ));


    }

    private function getSupplierMenuForMassAction(){
       $menu = array();

       //Supplier List
       $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');

       foreach ($collection as $item) {
           $menu[$item->getsup_id()] = $item->getsup_name();
       }

       return  $menu;
    }

}

