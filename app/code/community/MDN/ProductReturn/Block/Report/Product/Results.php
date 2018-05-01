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
class MDN_ProductReturn_Block_Report_Product_Results extends Mage_Adminhtml_Block_Widget_Grid
{

    public $_from = null;
    public $_to = null;
    public $_groupBy = null;

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));

        $this->_from    = date('Y-m-d', strtotime($this->getRequest()->getPost('from')));
        $this->_to      = date('Y-m-d', strtotime($this->getRequest()->getPost('to')));
        $this->_groupBy = $this->getRequest()->getPost('group_by');

        $this->setUseAjax(true);

        $this->setPagerVisibility(false);

        $this->setDefaultLimit(200);
    }

    /**
     *
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {

        //define periods
        $periods = Mage::helper('ProductReturn/Report')->setPeriods($this->_from, $this->_to, $this->_groupBy);

        $collection = Mage::getResourceModel('ProductReturn/Period_collection')->getProductReport();


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     *
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('sku', array(
            'header'   => Mage::helper('ProductReturn')->__('Sku'),
            'index'    => 'sku',
            'sortable' => false
        ));

        $this->addColumn('product', array(
            'header'   => Mage::helper('ProductReturn')->__('Product'),
            'index'    => 'rp_product_name',
            'sortable' => false
        ));

        $this->addColumn('period', array(
            'header'   => Mage::helper('ProductReturn')->__('Period'),
            'index'    => 'rrp_name',
            'filter'   => false,
            'align'    => 'center',
            'sortable' => false
        ));

        //add columns for reasons
        $reasons = Mage::helper('ProductReturn/Report')->getReasons($this->_from, $this->_to);
        $i       = 0;
        foreach ($reasons as $reason) {
            $this->addColumn('reason_' . $i, array(
                'header'         => $reason,
                'reason'         => $reason,
                'filter'         => false,
                'renderer'       => 'MDN_ProductReturn_Block_Widget_Column_Renderer_Report_Reason',
                'align'          => 'center',
                'sortable'       => false,
                'use_product_id' => true
            ));
            $i++;
        }

        $this->addColumn('total', array(
            'header'   => Mage::helper('ProductReturn')->__('Total'),
            'index'    => 'qty',
            'filter'   => false,
            'align'    => 'center',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGridAjax', array('_current' => true, 'from' => $this->_from, 'to' => $this->_to, 'group_by' => $this->_groupBy));
    }

}
