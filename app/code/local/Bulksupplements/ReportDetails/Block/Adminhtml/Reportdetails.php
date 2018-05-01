<?php

class Bulksupplements_ReportDetails_Block_Adminhtml_Reportdetails extends Mage_Adminhtml_Block_Widget_Grid_Container {



	public function __construct() {



		$this->_controller = 'adminhtml_reportdetails';

		$this->_blockGroup = 'reportdetails';

		$this->_headerText = Mage::helper('reportdetails')->__('Products Ordered by Kilogram');

		parent::__construct();

		$this->_removeButton('add');

	}



}

