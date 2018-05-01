<?php

class Extendware_EWCore_Block_Adminhtml_System_Information_Edit_Tab_Events extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
		$sortableData = array();
		$config = Mage::app()->getConfig();
		foreach ($config->getNode()->asArray() as $scope => $data) {
			if (in_array($scope, array('global', 'frontend', 'adminhtml')) === false) continue;
			if (isset($data['events']) === false) continue;
			if (is_array($data['events']) === false) continue;
		
			foreach ($data['events'] as $trigger => $event) {
				if (isset($event['observers']) === false) continue;
				if (is_array($event['observers']) === false) continue;
				$sortableData[$trigger] = array();
				foreach ($event['observers'] as $observer) {
					if (!isset($observer['class']) or !isset($observer['method'])) continue;
					$sortableData[$trigger][] = array(
						'trigger' => $trigger,
						'class' => $observer['class'],
						'method' => $observer['method'],
					);
				}
				if (empty($sortableData[$trigger])) unset($sortableData[$trigger]);
			}
		}

    	ksort($sortableData);
		$collection = new Varien_Data_Collection();
		foreach ($sortableData as $items) {
			foreach ($items as $data) {
				$collection->addItem(new Varien_Object($data));
			}
		}
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('trigger', array(
            'header'    => $this->__('Trigger'),
            'sortable'  => true,
            'index'     => 'trigger'
        ));

        $this->addColumn('class', array(
            'header'    => $this->__('Class'),
            'sortable'  => true,
            'index'     => 'class'
        ));
        
        $this->addColumn('method', array(
            'header'    => $this->__('Method'),
            'sortable'  => true,
            'index'     => 'method'
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/eventsGrid', array('_current'=>true));
    }
    
	public function getRowUrl($item)
    {
        return null;
    }
}