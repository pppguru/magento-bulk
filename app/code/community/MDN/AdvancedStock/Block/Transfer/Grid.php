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
class MDN_AdvancedStock_Block_Transfer_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('StockTransferGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setDefaultSort('st_created_at', 'desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge
        $collection = mage::getModel('AdvancedStock/StockTransfer')
                        ->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('st_created_at', array(
            'header' => Mage::helper('AdvancedStock')->__('Date'),
            'index' => 'st_created_at',
            'type' => 'date'
        ));

        $this->addColumn('st_name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'st_name'
        ));

        $this->addColumn('st_status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'st_status',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/StockTransfer')->getStatuses()
        ));


        $this->addColumn('st_source_warehouse', array(
            'header' => Mage::helper('AdvancedStock')->__('Source warehouse'),
            'index' => 'st_source_warehouse',
            'type' => 'options',
            'options'	=> mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align'	=> 'center'
        ));

        $this->addColumn('st_target_warehouse', array(
            'header' => Mage::helper('AdvancedStock')->__('Target warehouse'),
            'index' => 'st_target_warehouse',
            'type' => 'options',
            'options'	=> mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align'	=> 'center'
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $row->getId()));
    }

    /**
     *
     *
     */
    public function getNewUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Transfer/Edit', array());
    }

}
