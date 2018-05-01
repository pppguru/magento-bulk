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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Productreturn_Edit_Tab_ReservationGrid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
        $this->setUseAjax(true);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {

        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('type_id', 'simple')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('ProductReturn')->__('Id'),
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

        $this->addColumn('qty', array(
            'header' => Mage::helper('ProductReturn')->__('Stock'),
            'index'  => 'qty'
        ));

        Mage::dispatchEvent('productreturn_reservationgrid_preparecolumns', array('grid' => $this));

        $this->addColumn('reservation', array(
            'header'   => Mage::helper('ProductReturn')->__('Reservation'),
            'index'    => 'reservation',
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_Reservation',
            'rma_id'   => $this->getRma()->getId(),
            'align'    => 'center'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/ReservationGrid', array('_current' => true, 'rma_id' => $this->getRma()->getId()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }


    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
        //return $this->getUrl('ProductReturn/Admin/Edit', array('rma_id' => $row->getId()));
    }

    public function getRma()
    {
        return mage::registry('current_rma');
    }
}
