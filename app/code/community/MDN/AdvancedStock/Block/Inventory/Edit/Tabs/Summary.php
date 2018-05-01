<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_Summary extends Mage_Adminhtml_Block_Widget_Form
{
    //cache
    private $_brands = null;
    private $_suppliers = null;
    private $_stocks = null;
    private $_methods = null;
    private $_scanModes = null;
    private $_inventoryModes = null;

    
    
    
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $inventory = Mage::registry('current_inventory');
        $data = $inventory->getData();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('AdvancedStock')->__('General'))
        );

        $readonly = false;

        if (array_key_exists('ei_id',$data) && $data['ei_id']>0){
            $readonly  = true;
        }

        $fieldset->addField('ei_id', 'hidden', array(
            'name'=> 'ei_id',
            'label' => Mage::helper('AdvancedStock')->__('Id')
        ));
        
        $fieldset->addField('ei_date', 'label', array(
            'name'=>'ei_date',
            'label' => Mage::helper('AdvancedStock')->__('Created at')
        ));

        $fieldset->addField('ei_stock_picture_date', 'label', array(
            'name'=>'ei_stock_picture_date',
            'label' => Mage::helper('AdvancedStock')->__('Stock picture date')
        ));
        
        $fieldset->addField('ei_name', 'text', array(
            'name'=>'ei_name',
            'label' => Mage::helper('AdvancedStock')->__('Name'),
            'required' => true
        ));

        $fieldset->addField('ei_warehouse_id', 'select', array(
            'name'=>'ei_warehouse_id',
            'label' => Mage::helper('AdvancedStock')->__('Warehouse'),
            'required' => true,
            'values'=> $this->getWarehouses(),
            'disabled' => $readonly
        ));

        $fieldset->addField('ei_status', 'select', array(
            'name'=>'ei_status',
            'label' => Mage::helper('AdvancedStock')->__('Status'),
            'required' => true,
            'values'=> Mage::getModel('AdvancedStock/Inventory')->getStatuses(),
            //'disabled' => $readonly
        ));

        /*$fieldset->addField('ei_stock_take_partial', 'select', array(
            'name' => 'ei_stock_take_partial',
            'label' => Mage::helper('AdvancedStock')->__('Partial / Complete'),
            'required' => true,
            'values' => $this->getInventoryModes()
        ));*/

        $fieldset->addField('ei_stock_take_mode', 'select', array(
            'name' => 'ei_stock_take_mode',
            'label' => Mage::helper('AdvancedStock')->__('Process per location'),
            'required' => true,
            'values' => $this->getScanModes(),
            'disabled' => $readonly
        ));

        $fieldset->addField('ei_stock_take_method_code', 'select', array(
            'name' => 'ei_stock_take_method_code',
            'label' => Mage::helper('AdvancedStock')->__('Method'),
            'required' => true,
            'values' => $this->getMethods(),
            'onclick' => "showMethodData();",
            'disabled' => $readonly
        ));        

        $style = "display:none;";
        if(array_key_exists('ei_stock_take_method_code', $data)){
            $style = ($data['ei_stock_take_method_code'] == 'brand')?'"display:block;"':"display:none;";
            ($data['ei_stock_take_method_code'] == 'brand')?$data['brand_ei_stock_take_method_value'] = $data['ei_stock_take_method_value']:"";
        }
        $fieldset->addField('brand_ei_stock_take_method_value', 'select', array(
            'name' => 'brand_ei_stock_take_method_value',
            'label' => Mage::helper('AdvancedStock')->__('Manufacturers'),
            'style' => $style,
            'values'=> $this->getManufacturers(),
            'disabled' => $readonly
           
        ));

        if(array_key_exists('ei_stock_take_method_code', $data)){
            $style = ($data['ei_stock_take_method_code'] == 'supplier')?'"display:block;"':"display:none;";
            ($data['ei_stock_take_method_code'] == 'supplier')?$data['supplier_ei_stock_take_method_value'] = $data['ei_stock_take_method_value']:"";
        }
        $fieldset->addField('supplier_ei_stock_take_method_value', 'select', array(
            'name' =>'supplier_ei_stock_take_method_value',
            'label' => Mage::helper('AdvancedStock')->__('Suppliers'),
            'style' => $style,
            'values'=> $this->getSuppliers(),
            'disabled' => $readonly
        ));


        if(array_key_exists('ei_stock_take_method_code', $data)){
            $style = ($data['ei_stock_take_method_code'] == 'random')?'"display:block;"':"display:none;";
            $defaultValue = Mage::getStoreConfig('advancedstock/stock_take/random_default_value');           
            $data['random_ei_stock_take_method_value'] = ($data['ei_stock_take_method_code'] == 'random')?$data['ei_stock_take_method_value']:$defaultValue;
        }else{          
            $data['random_ei_stock_take_method_value'] = Mage::getStoreConfig('advancedstock/stock_take/random_default_value');
        }
        $fieldset->addField('random_ei_stock_take_method_value', 'text', array(
            'name' => 'random_ei_stock_take_method_value',
            'label' => Mage::helper('AdvancedStock')->__('Random number of products'),
            'style' => $style,
            'disabled' => $readonly
        ));


        $fieldset->addField('ei_comments', 'textarea', array(
            'name' => 'ei_comments',
            'label' => Mage::helper('AdvancedStock')->__('Comments')
        ));

        $form->setValues($data);
        $this->setForm($form);
        
        return $this;
    }

    /**
     * 
     * Get Stocktake methods
     */
    /*protected function getInventoryModes()
    {
       if ($this->_inventoryModes == null) {
            $methods = array();

            
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_COMPLETE ,'label' => Mage::helper('AdvancedStock')->__('Complete'));
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_PARTIAL ,'label' => Mage::helper('AdvancedStock')->__('Partial'));

            $this->_inventoryModes = $methods;
       }
       return $this->_inventoryModes;
    }*/

    /**
     *
     * Get Stocktake methods
     */
    protected function getScanModes()
    {
       if ($this->_scanModes == null) {
            $methods = array();

            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_BY_LOCATION ,'label' => Mage::helper('AdvancedStock')->__('Yes'));
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_BY_PRODUCT ,'label' => Mage::helper('AdvancedStock')->__('No'));

            $this->_scanModes = $methods;
       }
       return $this->_scanModes;
    }

    /**
     * //ERP-318
     * Get Stocktake methods
     */
    protected function getMethods()
    {
       if ($this->_methods == null) {
            $methods = array();

            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_FULL ,'label' => Mage::helper('AdvancedStock')->__('All products'));
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_BRAND ,'label' => Mage::helper('AdvancedStock')->__('For one manufacturer'));
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_SUPPLIER ,'label' => Mage::helper('AdvancedStock')->__('For one supplier'));
            $methods[] = array('value' => MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_RANDOM ,'label' => Mage::helper('AdvancedStock')->__('For X random products'));

            $this->_methods = $methods;
       }
       return $this->_methods;
    }

    /**
     * Get ERP warehouse list
     */
    protected function getWarehouses()
    {
       if ($this->_stocks == null) {
            $localWarehouses = array();

            $collection = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
            foreach($collection as $item)
            {
                $localWarehouses[] = array('value' => $item->getId() ,'label' => $item->getstock_name());
            }
            $this->_stocks = $localWarehouses;
       }
       return $this->_stocks;
    }

    /**
     * Get ERP warehouse list
     */
    protected function getSuppliers()
    {
       if ($this->_suppliers == null) {
            $localSuppliers = array();

            $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');

            $localSuppliers[] = array('value' => '' ,'label' => Mage::helper('AdvancedStock')->__('-- SELECT A SUPPLIER --'));


            foreach($collection as $item)
            {
                $localSuppliers[] = array('value' => $item->getsup_id() ,'label' => $item->getsup_name());
            }
            
            $this->_suppliers = $localSuppliers;
       }
       return $this->_suppliers;
    }

     /**
     * Get the list of product manufacturer if the mnuafacturer exists
     * mage::helper('AdvancedStock/Product_Base')->getManufacturerListForFilter()
     *
     * @return type
     */
    public function getManufacturers(){

        if ($this->_brands == null) {

            $list = array();

            $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();

            if($manufacturerCode){

              $productRessource = Mage::getModel('catalog/product')->getResource();

              $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                              ->setEntityTypeFilter($productRessource->getTypeId())
                              ->addFieldToFilter('attribute_code', $manufacturerCode);

              $attribute = $attributes->getFirstItem()->setEntity($productRessource);
              $manufacturers = $attribute->getSource()->getAllOptions(false);

              $list[] = array('value' => '' ,'label' => Mage::helper('AdvancedStock')->__('-- SELECT A MANUFACTURER --'));


              foreach ($manufacturers as $manufacturer) {
                  $list[] = array('value' => $manufacturer['value'] ,'label' => $manufacturer['label']);
              }

            }
            $this->_brands = $list;
        }

        return $this->_brands;
    }


    
}
