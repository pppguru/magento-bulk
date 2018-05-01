<?php

class Extendware_EWCrawler_Block_Adminhtml_Job_Edit_Tab_Log extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
    }

    protected function getJob()
    {
        return Mage::registry('ew:current_job');
    }

    protected function _prepareCollection()
    {
		$collection = new Varien_Data_Collection();
		$logs = explode("\n\n", $this->getJob()->getLog());
		foreach ($logs as $log) {
			@list($date, $message) = explode("\t", $log, 2);
			if ($date and $message) {
				$object = new Varien_Object();
				$object->setCreatedAt($date);
				$object->setMessage($message);
				
				$collection->addItem($object);
			}
		}

		$this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('created_at', array(
            'header'    => $this->__('Created'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
        
        $this->addColumn('message', array(
            'header'    => $this->__('Message'),
            'index'     => 'message',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));

        return parent::_prepareColumns();
    }

	public function getGridUrl()
    {
        return $this->getUrl('*/*/logGrid', array('_current'=>true));
    }
}