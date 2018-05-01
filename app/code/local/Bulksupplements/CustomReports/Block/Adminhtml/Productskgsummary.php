<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Productskgsummary extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		$this->_controller = 'adminhtml_productskgsummary';
		$this->_blockGroup = 'customreports';
		$this->_headerText = Mage::helper('customreports')->__('Total Sizes Sold');
		parent::__construct();
		$this->_removeButton('add');
	}

}
