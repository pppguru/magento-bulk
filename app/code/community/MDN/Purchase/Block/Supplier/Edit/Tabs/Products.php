<?php

class MDN_Purchase_Block_Supplier_Edit_Tabs_Products extends Mage_Adminhtml_Block_Widget_Grid {

    private $_supplier_id;

    /**
     * Set supplier
     *
     * @param unknown_type $value
     */
    public function setSupplierId($value) {
        $this->_supplier_id = $value;
        return $this;
    }

    /**
     * Get supplier
     *
     * @param unknown_type $value
     */
    public function getSupplierId() {
        return $this->_supplier_id;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('SupplierProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('supplier_products');
        $this->setDefaultSort('sku', 'asc');
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge les mouvements de stock
        $collection = mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name');

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
          $collection = $collection->addAttributeToSelect($manufacturerCode);
        }

        $collection->joinTable(mage::getModel('Purchase/Constant')->getTablePrefix() . 'purchase_product_supplier', 'pps_product_id=entity_id', array(
            'pps_reference' => 'pps_reference',
            'pps_product_id' => 'pps_product_id',
            'pps_supplier_num' => 'pps_supplier_num',
            'pps_is_default_supplier' => 'pps_is_default_supplier',
            'pps_quantity_product' => 'pps_quantity_product',
            'pps_last_price' => 'pps_last_price',
            'pps_can_dropship' => 'pps_can_dropship'
                )
        )
        ->addFieldToFilter('pps_supplier_num', $this->_supplier_id);

        

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Grid columns definition
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $this->addColumn('manufacturer', array(
                'header' => Mage::helper('purchase')->__('Manufacturer'),
                'index' => $manufacturerCode,
                'type' => 'options',
                'options' => $this->getManufacturersAsArray(),
            ));
        }
        
        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));

        $this->addColumn('pps_reference', array(
            'header' => Mage::helper('catalog')->__('Supplier sku'),
            'index' => 'pps_reference',
        ));

        $this->addColumn('pps_quantity_product', array(
            'header' => Mage::helper('catalog')->__('Supplier stock'),
            'index' => 'pps_quantity_product',
            'align' => 'center'
        ));

        $this->addColumn('pps_is_default_supplier', array(
            'header' => Mage::helper('purchase')->__('Is default'),
            'index' => 'pps_is_default_supplier',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('pps_can_dropship', array(
            'header' => Mage::helper('purchase')->__('Can dropship'),
            'index' => 'pps_can_dropship',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('pps_last_price', array(
            'header' => Mage::helper('purchase')->__('Price'),
            'index' => 'pps_last_price',
            'type' => 'number'
        ));

        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl('*/*/exportSupplierProductsCsv', array('_current'=>true, 'supplier_id' => $this->getSupplierId())),
                'label' => Mage::helper('AdvancedStock')->__('CSV')
            )
        );
        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl('*/*/exportSupplierProductsExcel', array('_current'=>true, 'supplier_id' => $this->getSupplierId())),
                'label' => Mage::helper('AdvancedStock')->__('Excel')
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/ProductsGrid', array('_current' => true, 'sup_id' => $this->_supplier_id));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $row->getId()));
    }

    
    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray() {
        $retour = array();

        $manufacturerCode = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeCode();

        if($manufacturerCode){
          //recupere la liste des manufacturers
          $product = Mage::getModel('catalog/product');
          $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                          ->setEntityTypeFilter($product->getResource()->getTypeId())
                          ->addFieldToFilter('attribute_code', $manufacturerCode)
                          ->load(false);

          $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
          $manufacturers = $attribute->getSource()->getAllOptions(false);

          //ajoute au menu
          foreach ($manufacturers as $manufacturer) {
              $retour[$manufacturer['value']] = $manufacturer['label'];
          }
        }

        return $retour;
    }

}
