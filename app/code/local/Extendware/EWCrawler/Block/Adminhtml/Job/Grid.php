<?php

class Extendware_EWCrawler_Block_Adminhtml_Job_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('job_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ewcrawler/job')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('job_id', array(
        	//'type'		=> 'number',
            'header'    => $this->__('ID'),
        	'index'     => 'job_id',
            'align'     => 'right',
            'width'     => '50px',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'index'     => 'status',
        	'type'      => 'options',
            'options'   => Mage::getSingleton('ewcrawler/job_data_option_status')->toGridOptionArray(),
        	'width'     => '100px',
        ));
        
        $this->addColumn('state', array(
            'header'    => $this->__('State'),
            'index'     => 'state',
        	'type'      => 'options',
            'options'   => Mage::getSingleton('ewcrawler/job_data_option_state')->toGridOptionArray(),
        	'width'     => '100px',
        ));

        $this->addColumn('percent_crawled', array(
        	'type' 		=> 'number',
            'header'    => $this->__('% Crawled'),
            'index'     => 'num_crawled_urls',
        	'width'		=> '50px',
        	'default'	=> ' ---- ',
        	'renderer' => 'Extendware_EWCrawler_Block_Adminhtml_Job_Grid_Renderer_PercentCrawled',
        	'filter'	=> false,
        	'sortable'	=> false,
        ));
        
        $this->addColumn('num_crawled_urls', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Crawled Urls'),
            'index'     => 'num_crawled_urls',
        	'width'		=> '50px',
        	'default'	=> ' ---- ',
        ));
        
        $this->addColumn('num_generated_urls', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Num Generated Urls'),
            'index'     => 'num_generated_urls',
        	'width'		=> '100px',
        	'default'	=> ' ---- ',
        ));
        
        $this->addColumn('num_logged_urls', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Num Logged Urls'),
            'index'     => 'num_logged_urls',
        	'width'		=> '100px',
        	'default'	=> ' ---- ',
        ));
        
        $this->addColumn('num_custom_urls', array(
        	'type' 		=> 'number',
            'header'    => $this->__('Num Custom Urls'),
            'index'     => 'num_custom_urls',
        	'width'		=> '100px',
        	'default'	=> ' ---- ',
        ));
		
        $this->addColumn('started_at', array(
            'header'    => $this->__('Date Started'),
            'index'     => 'started_at',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        ));
        
        $this->addColumn('scheduled_at', array(
            'header'    => $this->__('Date Scheduled'),
            'index'     => 'scheduled_at',
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
        $this->setMassactionIdField('job_id');
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
                         'values' => Mage::getSingleton('ewcrawler/job')->getStatusOptionModel()->toGridMassActionOptionArray()
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