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
class MDN_Purchase_Block_RemainingSupplyQuantities_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('RemainingSupplyQuantitiesGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('remaining_supply_quantities');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setDefaultLimit(500);		//temporary solution to fix page size issue
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		          
        $collection = Mage::getModel('catalog/product')
                                ->getCollection()
                                ->addAttributeToSelect('name')
                                ->addAttributeToFilter('waiting_for_delivery_qty', array('gt' => 0));
        
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if ($manufacturerCode)
            $collection->addAttributeToSelect($manufacturerCode);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * Defini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        
        if($manufacturerCode){
          $this->addColumn($manufacturerCode, array(
              'header'=> Mage::helper('purchase')->__('Manufacturer'),
              'index' => $manufacturerCode,
              'filter_index' => $manufacturerCode,
              'type' => 'options',
              'options' => mage::helper('AdvancedStock/Product_Base')->getManufacturerListForFilter()
          ));
        }
             
        $this->addColumn('sku', array(
            'header'=> Mage::helper('purchase')->__('Sku'),
            'index' => 'sku',
            'renderer' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_Sku',
        ));
                            
        $this->addColumn('name', array(
            'header'=> Mage::helper('purchase')->__('Name'),
            'index' => 'name'
        ));
        
        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this, 'pop_product_id');

              
        $this->addColumn('stocks', array(
            'header'=> Mage::helper('purchase')->__('Available stock'),
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_Stocks'
        ));
                                    
        $this->addColumn('expected_qty', array(
            'header'=> Mage::helper('purchase')->__('Expected qty'),
            'index' => 'waiting_for_delivery_qty',
            'align' => 'center',
            'type' => 'number'
        ));
                         
        $this->addColumn('pending_qty', array(
            'header'=> Mage::helper('purchase')->__('Pending customer<br>orders qty'),
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_PendingCustomerOrdersQty',
            'filter' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Filter_PendingQty',
            'index' => 'entity_id',
            'filter_index' => 'entity_id'
        ));
                       
        $this->addColumn('purchase_orders', array(
            'header'=> Mage::helper('purchase')->__('Purchase orders'),
            'index' => 'entity_id',
            'sortable' => false,
            'renderer' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_PurchaseOrder',
            'filter' => 'MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Filter_PurchaseOrder'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

}