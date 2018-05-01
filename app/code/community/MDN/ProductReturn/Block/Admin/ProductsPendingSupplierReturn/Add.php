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
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Admin_ProductsPendingSupplierReturn_Add extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductsPendingSupplierReturnAdd');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
        $this->setDefaultSort('rsrp_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection des produits en attentes
     */

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Catalog/Product')
            ->getCollection()
            ->AddAttributeToSelect('name')
            ->addAttributeToSelect(mage::getStoreConfig('productreturn/supplier_return/manufacturer_attribute_name'));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Définit les colonnes du grip
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('ProductReturn')->__('ID'),
            'index'  => 'entity_id',
            'width'  => '100px'
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('ProductReturn')->__('Sku'),
            'index'  => 'sku'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('ProductReturn')->__('Name'),
            'index'  => 'name'
        ));

        $this->addColumn('manufacturer', array(
            'header'  => Mage::helper('ProductReturn')->__('Manufacturer'),
            'index'   => 'manufacturer',
            'type'    => 'options',
            'options' => $this->getManufacturerOptions()
        ));

        $this->addColumn('decrement', array(
            'header'   => Mage::helper('ProductReturn')->__('Stock Decrement'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnStockDecrement',
            'filter'   => false,
            'sortable' => false
        ));

        $this->addColumn('actionadd', array(
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnAddRsrp',
            'filter'   => false,
            'sortale'  => false
        ));

        return parent::_prepareColumns();

    }

    public function getManufacturerOptions()
    {
        $retour = array();

        $product       = Mage::getModel('catalog/product');
        $attributes    = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($product->getResource()->getTypeId())
            ->addFieldToFilter('attribute_code', mage::getStoreConfig('productreturn/supplier_return/manufacturer_attribute_name'));
        $attribute     = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        foreach ($manufacturers as $manufacturer) {
            $retour[$manufacturer['value']] = $manufacturer['label'];
        }

        return $retour;
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

}