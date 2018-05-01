<?php

//deprecated since ERP 2.9.5.3, still here to prevent retro compatibility with previous version having a conflict fixed with another extension

class MDN_AdvancedStock_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{


	/**
	 * Add After Column
	 *
	 * @param unknown_type $columnId
	 * @param unknown_type $column
	 * @param unknown_type $indexColumn
	 * @return unknown
	 */
	/*
	public function addAfterColumn($columnId, $column,$indexColumn) {
		$columns = array();
		foreach ($this->_columns as $gridColumnKey => $gridColumn) {
			$columns[$gridColumnKey] = $gridColumn;
			if($gridColumnKey == $indexColumn) {
				$columns[$columnId] = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
		                ->setData($column)
		                ->setGrid($this);
		        $columns[$columnId]->setId($columnId);         
			}
		}
		$this->_columns = $columns;
        return $this;
	}
	*/
	
	/**
	 * Add Before column
	 *
	 * @param unknown_type $columnId
	 * @param unknown_type $column
	 * @param unknown_type $indexColumn
	 * @return unknown
	 */
	/*
	public function addBeforeColumn($columnId, $column,$indexColumn) {
		$columns = array();
		foreach ($this->_columns as $gridColumnKey => $gridColumn) {
			if($gridColumnKey == $indexColumn) {
				$columns[$columnId] = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
		                ->setData($column)
		                ->setGrid($this);
		        $columns[$columnId]->setId($columnId);         
			}
			$columns[$gridColumnKey] = $gridColumn;
		}
		$this->_columns = $columns;
        return $this;
	}
	*/
		
	/**
	 * Manage columns
	 *
	 */
	/*
	protected function _prepareColumns()
    {
		parent::_prepareColumns();
        
    	$this->addAfterColumn('increment_id', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
       		'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'order',
            'filter' => false,
            'sortable' => false
        ),'real_order_id');
		
        $this->addAfterColumn('payment_validated', array(
            'header'=> Mage::helper('AdvancedStock')->__('Payment validated'),
            'width' => '40px',
            'index' => 'payment_validated',
            'align' => 'center',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('purchase')->__('Yes'),
                '0' => Mage::helper('purchase')->__('No'),
            ),
        ),'status');
             

        //raise event to allow other modules to add columns
        Mage::dispatchEvent('salesorder_grid_preparecolumns', array('grid'=>$this));
        
    }
	*/

    /**
     * Manage mass actions
     * 
     * @return unknown
     */
	/*
    protected function _prepareMassaction()
    {
    	parent::_prepareMassaction();
    	
        $this->getMassactionBlock()
        	->addItem('validate_payment', array(
             'label'=> Mage::helper('AdvancedStock')->__('Validate payment'),
             'url'  => $this->getUrl('adminhtml/AdvancedStock_Misc/Validatepayment'),))
        	->addItem('cancel_payment', array(
             'label'=> Mage::helper('AdvancedStock')->__('Cancel payment'),
             'url'  => $this->getUrl('adminhtml/AdvancedStock_Misc/Cancelpayment'),))
        ;

        //raise event to allow other extension to add columns
        Mage::dispatchEvent('salesorder_grid_preparemassaction', array('grid'=>$this));
        
        return $this;
    }
	*/

}

?>