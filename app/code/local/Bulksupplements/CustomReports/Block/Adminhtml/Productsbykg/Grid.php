<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Productsbykg_Grid extends Mage_Adminhtml_Block_Report_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('productsbykgreportgrid');
		$this->setTemplate('customreports/productsbykg/grid.phtml');

		/*$this->setDefaultSort('created_at');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);*/
	}

	protected function _prepareCollection() {
		parent::_prepareCollection();
		$collections = $this->getCollection()->initReport('customreports/productsbykg');

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



		$this->addColumn('ordered_qty', array(
			'header'    =>Mage::helper('reports')->__('Units Sold'),
			'width'     =>'120px',
			'align'     =>'left',
			'index'     =>'ordered_qty',
//			'total'     =>'sum',
			'type'      =>'number'
		));

		$this->addColumn('kg', array(
			'header'    =>Mage::helper('reports')->__('Kg'),
			'width'     =>'120px',
			'align'     =>'right',
			'index'     =>'kg',
//            'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Kg(),
			'renderer' => new Bulksupplements_CustomReports_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg(),
			'total'     =>'sum'
		));



		$this->addExportType('*/*/exportCsv', Mage::helper('customreports')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customreports')->__('XML'));
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
