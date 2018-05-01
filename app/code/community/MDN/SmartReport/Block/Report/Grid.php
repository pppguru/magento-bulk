<?php

class MDN_SmartReport_Block_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('SmartReport')->__('No reports'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('SmartReport/Report')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'=> Mage::helper('SmartReport')->__('#'),
            'index' => 'id'
        ));

        $this->addColumn('created_at', array(
            'header'=> Mage::helper('SmartReport')->__('Date'),
            'index' => 'created_at',
            'tyoe' => 'date'
        ));

        $this->addColumn('type', array(
            'header'=> Mage::helper('SmartReport')->__('Type'),
            'index' => 'type'
        ));

        $this->addColumn('name', array(
            'header'=> Mage::helper('SmartReport')->__('Name'),
            'index' => 'name'
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

    public function getNewUrl()
    {
        return $this->getUrl('*/*/Edit');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/Edit', array('id' => $row->getId()));
    }
}
