<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_FixedProducts extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('InventoryFixedProducts');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setUseAjax(true);
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $inventory = Mage::registry('current_inventory');
        
        $collection = $inventory->getFixedProducts();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {
                
                      
        $this->addColumn('sm_date', array(
            'header' => Mage::helper('AdvancedStock')->__('Date'),
            'index' => 'sm_date',
            'type' => 'datetime',
            'sortable' => true
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku',
            'sortable' => true
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('AdvancedStock')->__('Product'),
            'index' => 'value',
            'sortable' => true
        ));

        $this->addColumn('sm_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Qty'),
            'index' => 'sm_qty',
            'align' => 'center',
            'type' => 'number',
            'sortable' => true
        ));

        $this->addColumn('sm_source_stock', array(
            'header' => Mage::helper('AdvancedStock')->__('From warehouse'),
            'index' => 'sm_source_stock',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align' => 'center'
        ));

        $this->addColumn('sm_target_stock', array(
            'header' => Mage::helper('AdvancedStock')->__('To warehouse'),
            'index' => 'sm_target_stock',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align' => 'center'
        ));



        $this->addColumn('sm_description', array(
            'header' => Mage::helper('AdvancedStock')->__('Description'),
            'index' => 'sm_description',
            'sortable' => true
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('AdvancedStock')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getentity_id',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('AdvancedStock')->__('View product'),
                        'url'     => array('base'=>'AdvancedStock/Products/Edit'),
                        'field'   => 'product_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));
             

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        $inventory = Mage::registry('current_inventory');
        return $this->getUrl('adminhtml/AdvancedStock_Inventory/AjaxScannedProducts', array('ei_id' => $inventory->getId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
