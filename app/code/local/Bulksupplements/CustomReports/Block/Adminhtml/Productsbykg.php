<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Productsbykg extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		$this->_controller = 'adminhtml_productsbykg';
		$this->_blockGroup = 'customreports';
		$this->_headerText = Mage::helper('customreports')->__('Products Ordered by Kilogram');
		parent::__construct();
		$this->_removeButton('add');
	}

}
