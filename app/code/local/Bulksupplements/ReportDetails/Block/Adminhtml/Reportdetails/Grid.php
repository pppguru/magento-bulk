<?php

class Bulksupplements_ReportDetails_Block_Adminhtml_Reportdetails_Grid extends Mage_Adminhtml_Block_Report_Grid {



	public function __construct() {

		parent::__construct();

		$this->setId('reportdetailsGrid');

		$this->setTemplate('reportdetails/grid.phtml');



		/*$this->setDefaultSort('created_at');

		$this->setDefaultDir('ASC');

		$this->setSaveParametersInSession(true);

		$this->setSubReportSize(false);*/

	}



	protected function _prepareCollection() {

		parent::_prepareCollection();

		$collections = $this->getCollection()->initReport('reportdetails/reportdetails');
		
		Mage::getSingleton('core/session')->setParentProductId($this->getFilter('product_id'));
		
		
		return $this;

	}



	protected function _prepareColumns() {

		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'weight_form');

		$options =  array();

		foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {

			$options[$option['value']] = str_replace('Pure Powder', '', $option['label']);

		}



		$this->addColumn('name', array(

			'header'    =>Mage::helper('reports')->__('Product Name'),

			'index'     =>'name'

		));

		/*$this->addColumn('sku', array(

			'header'    =>Mage::helper('reports')->__('SKU'),

			'index'     =>'sku'

		));*/



		$this->addColumn('weight_form', array(

			'header'    =>Mage::helper('reports')->__('Size'),

			'width'     =>'120px',

//			'align'     =>'right',

			'index'     =>'weight_form',

			'type'      => 'options',

			'options' => $options

		));



		/*$baseCurrencyCode = $this->getCurrentCurrencyCode();



		$this->addColumn('price', array(

			'header'        => Mage::helper('reports')->__('Price'),

			'width'         => '120px',

			'type'          => 'currency',

			'currency_code' => $baseCurrencyCode,

			'index'         => 'price',

			'rate'          => $this->getRate($baseCurrencyCode),

		));*/



		$this->addColumn('amazon_fba_units', array(

			'header'    =>Mage::helper('reports')->__('Amazon FBA</br> Units'),

			'index'     =>'amazon_fba_units',
			
			'align'     =>'right',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_fba_units')
			
		));

		$this->addColumn('amazon_fba_kg', array(

			'header'    =>Mage::helper('reports')->__('Amazon FBA</br> Kg'),

			'width'     =>'120px',

			'align'     =>'right',

			'index'     =>'kg',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_fba_kg'),

			'total'     =>'sum'

		));	
		
		$this->addColumn('amazon_fba_percentage', array(

			'header'    =>Mage::helper('reports')->__('Amazon FBA %'),

			'index'     =>'amazon_fba_percentage',
			
			'align'     =>'right',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_fba_percentage')
			

		));

		$this->addColumn('amazon_units', array(

			'header'    =>Mage::helper('reports')->__('Amazon Merchant</br> Units'),

			'index'     =>'amazon_units',
			
			'align'     =>'right',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_units'),


		));

		$this->addColumn('amazon_merchant_kg', array(

			'header'    =>Mage::helper('reports')->__('Amazon Merchant</br> Kg'),

			'width'     =>'120px',

			'align'     =>'right',

			'index'     =>'kg',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_merchant_kg'),

			//'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg(),

			'total'     =>'sum'

		));	
		
		$this->addColumn('amazon_merchant_percentage', array(

			'header'    =>Mage::helper('reports')->__('Amazon Merchant %'),

			'index'     =>'amazon_merchant_percentage',
			
			'align'     =>'right',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('amazon_merchant_percentage'),

		));
		
		$this->addColumn('bulkSupplements', array(

			'header'    =>Mage::helper('reports')->__('BulkSupplements.com</br> Units'),
			
			'align'     =>'right',

			'index'     =>'bulkSupplements'

		));

		$this->addColumn('bulkSupplements_kg', array(

			'header'    =>Mage::helper('reports')->__('BulkSupplements.com</br> Kg'),

			'width'     =>'120px',

			'align'     =>'right',

			'index'     =>'kg',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('bulkSupplements_kg'),

           // 'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Kg(),

			//'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg(),

			'total'     =>'sum'

		));	
		
		$this->addColumn('bulkSupplements_percentage', array(

			'header'    =>Mage::helper('reports')->__('BulkSupplements.com %'),

			'index'     =>'bulkSupplements_percentage',
			
			'align'     =>'right',
			
			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg('bulkSupplements_percentage'),

		));



		$this->addColumn('ordered_qty', array(

			'header'    =>Mage::helper('reports')->__('Total Overall</br> Units'),

			'width'     =>'120px',

			'align'     =>'left',

			'index'     =>'ordered_qty',

//			'total'     =>'sum',

			'type'      =>'number'

		));



		$this->addColumn('kg', array(

			'header'    =>Mage::helper('reports')->__('Total Overall</br> Kg'),

			'width'     =>'120px',

			'align'     =>'right',

			'index'     =>'kg',

//            'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Kg(),

			'renderer' => new Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg(),

			'total'     =>'sum'

		));


		/*$this->addColumn('total_overall_percentage', array(

			'header'    =>Mage::helper('reports')->__('Total Overall %'),

			'index'     =>'percentage'

		));
*/

		$this->addExportType('*/*/exportCsv', Mage::helper('reportdetails')->__('CSV'));

		$this->addExportType('*/*/exportXml', Mage::helper('reportdetails')->__('XML'));

		return parent::_prepareColumns();

	}



	public function getRowUrl($row) {

		return false;

	}



	public function getReport($from, $to) {

		if ($from == '') {

			$from = $this->getFilter('report_from');

		}

		if ($to == '') {

			$to = $this->getFilter('report_to');

		}



		$totalObj = Mage::getModel('reports/totals');

		$totals = $totalObj->countTotals($this, $from, $to);



		$this->setTotals($totals);

		$this->addGrandTotals($totals);



		return $this->getCollection()->getReport($from, $to);

	}



}

