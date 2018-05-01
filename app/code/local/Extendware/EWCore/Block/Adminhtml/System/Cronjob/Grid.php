<?php

class Extendware_EWCore_Block_Adminhtml_System_Cronjob_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('scheduled_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->_getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('schedule_id', array(
			'header' => $this->__('ID'),
			'width' => '50px',
			'type' => 'text',
			'index' => 'schedule_id',
        	'align' => 'right'
		));
		
		$this->addColumn('status', array(
			'header' => $this->__('Status'),
			'width' => '50px',
			'type' => 'options',
			'index' => 'status',
			'options' => Mage::getSingleton('ewcore/adminhtml_data_option_cron_status')->toGridOptionArray()
		));
		
		$this->addColumn('job_code', array(
			'header' => $this->__('Code'),
			'width' => '200px',
			'type' => 'options',
			'index' => 'job_code',
			'options' => Mage::getSingleton('ewcore/adminhtml_data_option_cron_code')->reorder('value', 'asc')->toGridOptionArray()
		));

		$this->addColumn('scheduled_at', array(
			'header' => $this->__('Scheduled For'),
			'index' => 'scheduled_at',
			'type' => 'datetime',
			'width' => '155px',
			'default' => ' ---- ',
			'renderer' => 'ewcore/adminhtml_widget_grid_column_renderer_datetime',
		));

		$this->addColumn('executed_at', array(
			'header' => $this->__('Executed'),
			'index' => 'executed_at',
			'type' => 'datetime',
			'width' => '155px',
			'default' => ' ---- ',
			'renderer' => 'ewcore/adminhtml_widget_grid_column_renderer_datetime',
		));

		$this->addColumn('finished_at', array(
			'header' => $this->__('Finished'),
			'index' => 'finished_at',
			'type' => 'datetime',
			'width' => '155px',
			'default' => ' ---- ',
			'default' => ' ---- ',
			'renderer' => 'ewcore/adminhtml_widget_grid_column_renderer_datetime',
		));
		
		
		$this->addColumn('messages', array(
			'header' => $this->__('Messages'),
			'type' => 'text',
			'index' => 'messages',
		));
		
		
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function _prepareMassaction(){
        $this->setMassactionIdField('system_message_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));
        
        return $this;
    }
    
	protected function _getCollection()
	{
		if ($this->_collection === null) {
			$collection = Mage::getModel('cron/schedule')->getCollection();
	        $this->setCollection($collection);
		}

		return $this->_collection;
	}
}