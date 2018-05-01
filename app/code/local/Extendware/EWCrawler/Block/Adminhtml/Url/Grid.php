<?php

class Extendware_EWCrawler_Block_Adminhtml_Url_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('url_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ewcrawler/url')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('url_id', array(
        	//'type'		=> 'number',
            'header'    => $this->__('ID'),
        	'index'     => 'url_id',
            'align'     => 'right',
            'width'     => '50px',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'index'     => 'status',
        	'type'      => 'options',
            'options'   => Mage::getSingleton('ewcrawler/url_data_option_status')->toGridOptionArray(),
        	'width'     => '100px',
        ));
        
        $this->addColumn('protocol', array(
            'header'    => $this->__('Protocol'),
            'index'     => 'protocol',
        	'type'      => 'options',
            'options'   => Mage::getSingleton('ewcrawler/url_data_option_protocol')->toGridOptionArray(),
        	'width'     => '100px',
        ));
        
        $this->addColumn('path', array(
            'header'    => $this->__('Path'),
            'index'     => 'path',
        ));
        
        $this->addColumn('cookies', array(
            'header'    => $this->__('Cookies'),
            'index'     => 'cookies',
        ));
        
        $this->addColumn('store_ids', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Store Ids'),
            'index'     => 'store_ids',
        	'width'		=> '100px',
        	'default'	=> ' ---- ',
        ));
		
        $this->addColumn('customer_group_ids', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Customer Group Ids'),
            'index'     => 'customer_group_ids',
        	'width'		=> '100px',
        	'default'	=> ' ---- ',
        ));
        
        $this->addColumn('created_at', array(
            'header'    => $this->__('Date Created'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        ));
        
        $this->addColumn('action', array(
			'header' => $this->__('Action'),
			'width' => '50px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => $this->__('Edit'),
					'url' => array('base' => '*/*/edit'), 'field' => 'id'
				)
				
			),
			'filter' => false,
			'sortable' => false,
			'is_system' => true
		));
		
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

	protected function _prepareMassaction() {
        $this->setMassactionIdField('url_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('status', array(
			'label' => $this->__('Change status'),
			'url' => $this->getUrl('*/*/massStatus'),
			'confirm' => $this->__('Are you sure you want to change the status?'),
			'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => $this->__('Status'),
                         'values' => Mage::getSingleton('ewcrawler/url')->getStatusOptionModel()->toGridMassActionOptionArray()
                     )
             )
		));
		
        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $this->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $this->__('Are you sure?')
        ));

        return $this;
    }
}