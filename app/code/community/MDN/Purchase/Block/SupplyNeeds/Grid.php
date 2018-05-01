<?php

class MDN_Purchase_Block_SupplyNeeds_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    private $_mode = null;
    private $_orderId = null;

    public function __construct() {
        parent::__construct();
        $this->setId('SupplyNeedsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('supply_needs');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));

        $this->setDefaultSort('sn_status', 'asc');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setUseAjax(true);
    }

    public function setMode($mode, $orderId) {
        $this->_mode = $mode;
        $this->_orderId = $orderId;
    }
    

    protected function _prepareCollection() {

        $collection = mage::getResourceModel('Purchase/SupplyNeeds_NewCollection');
        $collection->setWarehouseId($this->getCurrentWarehouse());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('manufacturer_id', array(
            'header' => Mage::helper('purchase')->__('Manufacturer'),
            'index' => 'manufacturer_id',
            'align' => 'center',
            'type' => 'options',
            'options' => $this->getManufacturersAsArray(),
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'align' => 'center',
            'index' => 'sku'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('purchase')->__('Name'),
            'align' => 'center',
            'index' => 'name'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('purchase')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_ProductStatus',
            'options' => Mage_Catalog_Model_Product_Status::getOptionArray()
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this, 'product_id');

        $this->addColumn('supply_need_status', array(
            'header' => Mage::helper('purchase')->__('Status'),
            'index' => 'sn_status',
            'align' => 'center',
            'type' => 'options',
            'options' => mage::getModel('Purchase/SupplyNeeds')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supply_needs/display_details_column'))
        {
            $this->addColumn('sn_details', array(
                'header' => Mage::helper('purchase')->__('Details'),
                'index' => 'sn_details',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'product_id_field_name' => 'product_id',
                'product_name_field_name' => 'name',
                'is_system' => true
            ));
        }

        $this->addColumn('sn_needed_qty', array(
            'header' => Mage::helper('purchase')->__('Qty'),
            'index' => 'qty_min',
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_NeededQty',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('qty_for_po', array(
            'header' => Mage::helper('purchase')->__('Qty for PO'),
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_QtyForPo',
            'filter' => false,
            'sortable' => false,
            'is_system' => true
        ));


        $this->addColumn('waiting_for_delivery_qty', array(
            'header' => Mage::helper('purchase')->__('Waiting for<br>delivery'),
            'index' => 'waiting_for_delivery_qty',
            'type' => 'number'
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history/sales_history')) {

            if (mage::getStoreConfig('purchase/purchase_product_grid/display_sales_history')) {
                $this->addColumn('sales_history', array(
                    'header' => Mage::helper('purchase')->__('Sales history'),
                    'index' => 'entity_id',
                    'align' => 'center',
                    'filter' => false,
                    'sortable' => false,
                    'renderer' => $this->getSalesHistoryRenderer()
                ));
            }

            $this->addColumn('avg_sales_week', array(
                'header' => Mage::helper('purchase')->__('Average Sales per week'),
                'align' => 'center',
                'index' => 'avg_sales_week',
                'type' => 'number'
            ));

            $this->addColumn('run_out', array(
                'header' => Mage::helper('purchase')->__('run out (days)'),
                'align' => 'center',
                'index' => 'run_out',
                'type' => 'number'
            ));

            $this->addColumn('lead_time', array(
                'header' => Mage::helper('purchase')->__('Lead time (days)'),
                'align' => 'center',
                'index' => 'pa_supply_delay',
                'type' => 'number'
            ));

            $this->addColumn('purchase_before', array(
                'header' => Mage::helper('purchase')->__('Purchase before (days)'),
                'align' => 'center',
                'index' => 'purchase_before',
                'type' => 'number'
            ));
        }

        $this->addColumn('stock_summary', array(
            'header' => Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'entity_id',
            'align' => 'center',
            'renderer' => $this->getStockSummaryRenderer(),
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary',
            'sortable' => false
        ));

        $this->addColumn('sn_suppliers_name', array(
            'header' => Mage::helper('purchase')->__('Suppliers'),
            'index' => 'product_id',
            'filter' => 'Purchase/Widget_Column_Filter_SupplyNeeds_Suppliers',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_Suppliers',
            'sortable' => false
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('purchase')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getproduct_id',
            'actions' => array(
                array(
                    'caption' => Mage::helper('purchase')->__('View'),
                    'url' => array('base' => 'adminhtml/AdvancedStock_Products/Edit'),
                    'field' => 'product_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('adminhtml/Purchase_SupplyNeeds/exportCsvSupplyNeed', Mage::helper('purchase')->__('CSV'));

        $this->addExportType('adminhtml/Purchase_SupplyNeeds/exportExcelSupplyNeed', Mage::helper('purchase')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Url to refresh grid with ajax
     */
    public function getGridUrl() {
        return $this->getUrl('adminhtml/Purchase_SupplyNeeds/AjaxGrid', array('_current' => true, 'po_num' => $this->_orderId, 'mode' => $this->_mode));
    }
    
    //prevent to get back to the top of the page when we click on popup
    public function getRowUrl($row) {
            return "#" . $row->getId();
    }

    protected function getSalesHistoryRenderer(){
        if($this->getCurrentWarehouse()) {
            return 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistoryByWarehouse';
        }else{
            return 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistoryGlobal';
        }
    }

    protected function getStockSummaryRenderer(){
       return 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_StockSummary';
    }

    /**
     * Return suppliers list as array
     *
     */
    public function getSuppliersAsArray() {
        $supArray = array();

        $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');
        foreach ($collection as $item) {
            $supArray[$item->getsup_id()] = $item->getsup_name();
        }
        return $supArray;
    }

    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray()
    {
        $manufacturersArray = array();

        $manufacturerAttributeId = Mage::getStoreConfig('purchase/supplyneeds/manufacturer_attribute');
        if ($manufacturerAttributeId){
            $product = Mage::getModel('catalog/product');
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('main_table.attribute_id', $manufacturerAttributeId)
                ->load(false);
            $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
            $manufacturers = $attribute->getSource()->getAllOptions(false);

            foreach ($manufacturers as $manufacturer) {
                $manufacturersArray[$manufacturer['value']] = $manufacturer['label'];
            }
        }

        return $manufacturersArray;
    }

    /**
     * Get warehouses allowed fro supplyNeed
     */
    public function getWarehouses() {
        return Mage::getModel('AdvancedStock/Warehouse')
            ->getCollection()
            ->addFieldToFilter('stock_disable_supply_needs', 0);
    }


    /**
     * Get warehouse selected in dropdown
     */
    public function getCurrentWarehouse(){
        return Mage::getSingleton('adminhtml/session')->getData('supply_needs_warehouse');
    }



}
