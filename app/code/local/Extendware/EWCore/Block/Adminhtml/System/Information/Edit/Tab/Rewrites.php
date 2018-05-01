<?php

class Extendware_EWCore_Block_Adminhtml_System_Information_Edit_Tab_Rewrites extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

	protected function _beforeToHtmlHtml() {
		$html = null;
		if ($this->canDisplayContainer()) {
			$html = '<div id="messages"><ul class="messages"><li class="notice-msg"><ul><li><span>';
			$html .= $this->__('You can view what classes are being rewritten by the system. This is useful when debugging extension conflicts.');
			$html .= '</span></li></ul></li></ul></div>';
		}
    	return $html;
    }
    
    protected function _prepareCollection()
    {
    	$sortableData = array();
		$config = Mage::app()->getConfig();
		foreach ($config->getNode('global')->asArray() as $type => $data) {
			if (in_array($type, array('models', 'blocks', 'helpers')) === false) continue;
			foreach ($data as $module => $item) {
				if (isset($item['rewrite']) === false) continue;
				if (is_array($item['rewrite']) === false) continue;
				foreach ($item['rewrite'] as $source => $rewriteClass) {
					$classKey = $module . '/' . $source;
					$resultingClass = null;
					if ($type == 'models') $resultingClass = Mage::app()->getConfig()->getModelClassName($classKey);
					elseif ($type == 'blocks') $resultingClass = Mage::app()->getConfig()->getBlockClassName($classKey);
					elseif ($type == 'helpers') $resultingClass = Mage::app()->getConfig()->getHelperClassName($classKey);
					$sortableData[$classKey] = array(
						'type' => $type,
						'class_key' => $classKey,
						'rewrite_class' => $rewriteClass,	
						'resulting_class' => $resultingClass,
					);
				}
			}
		}
		
		ksort($sortableData);
		$collection = new Varien_Data_Collection();
		foreach ($sortableData as $data) {
			$collection->addItem(new Varien_Object($data));
		}

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('type', array(
            'header'    => $this->__('Type'),
            'sortable'  => true,
            'width'     => '70px',
            'index'     => 'type'
        ));

        $this->addColumn('class_key', array(
            'header'    => $this->__('Class Key'),
            'sortable'  => true,
            'index'     => 'class_key'
        ));
        
        $this->addColumn('rewrite_class', array(
            'header'    => $this->__('Rewrite Class'),
            'sortable'  => true,
            'index'     => 'rewrite_class'
        ));
        
        $this->addColumn('resulting_class', array(
            'header'    => $this->__('Resulting Class'),
            'sortable'  => true,
            'index'     => 'resulting_class'
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/rewritesGrid', array('_current'=>true));
    }
    
	public function getRowUrl($item)
    {
        return null;
    }
}