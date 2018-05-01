<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Productskgsummary_Grid extends Mage_Adminhtml_Block_Report_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('productskgsummaryreportgrid');
		$this->setTemplate('customreports/productskgsummary/grid.phtml');

		/*$this->setDefaultSort('created_at');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);*/
	}

	protected function _prepareCollection() {
		parent::_prepareCollection();
		$collections = $this->getCollection()->initReport('customreports/productskgsummary');

		return $this;
	}

	protected function _prepareColumns() {
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'weight_form');
		$options =  array();
		foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
			// $options[$option['value']] = str_replace('Pure Powder', '', $option['label']);
			$options[$option['value']] = $option['label'];
		}

		// $this->addColumn('name', array(
		// 	'header'    =>Mage::helper('reports')->__('Product Name'),
		// 	'index'     =>'name'
		// ));
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
			'align'     =>'right',
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

	/**
	 * Check if report period is set or not
	 * 2 Dec 2016, Erik
	 * @return Boolean
	 */
	public function isSetDateRange() {
		$from = $this->getFilter('report_from');
		$to = $this->getFilter('report_to');

		return $from != '' && $to != '';
	}

	/**
	 * Get all sizes of products
	 * 2 Dec 2016, Erik
	 * @return Array
	 */
	public function getAllSizes() {
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'weight_form');
		$options =  array();
		foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
			$label = $option['label'];
			$isShown = false;
			if (strtolower(substr($label, -11)) == 'pure powder' || strtolower(substr($label, -19)) == 'pure powder (vegan)'):
				$isShown = true;
			elseif (strtolower(substr($label, -8)) == 'capsules'):
				$isShown = true;
			elseif (strtolower(substr($label, -8)) == 'softgels'):
				$isShown = true;
			endif;
			if (!$isShown) continue;
			$options[] = $label;
		}
		return array_unique($options);
	}

	/**
	 * Calculate the total units sold and kg by size, period
	 * 2 Dec 2016, Erik
	 * @return Array
	 */
	public function getSummaryData() {
		$_summaryData = array();
		$_intervals = $this->getCollection()->getIntervals();

		foreach ($_intervals as $_index => $_item):
			$report=$this->getReport($_item['start'], $_item['end']);

			$i=0;
			foreach ($report as $_subIndex=>$_subItem):
				$_size = '';
				$_unitsSold = 0;
				$_kg = 0;
				foreach ($this->_columns as $_columnName => $_column):
					if ($_columnName == 'weight_form'):
						$_size = $_column->getRowField($_subItem);
					elseif ($_columnName == 'ordered_qty'):
						$_unitsSold = $_column->getRowField($_subItem);
					elseif ($_columnName == 'kg'):
						$_kg = $_column->getRowField($_subItem);
					endif;
				endforeach;

				$_summaryData[$_index][$_size]['ordered_qty'] += $_unitsSold;
				$_summaryData[$_index][$_size]['kg'] += $_kg;
				$_summaryData[$_index][$_size]['weight_form'] = $_size;
			endforeach;
		endforeach;

		return $_summaryData;
	}

	/**
	 * Retrieve grid as CSV
	 * 2 Dec 2016, Erik
	 * @return unknown
	 */
	public function getCsv()
	{
		$csv = '';
		$this->_prepareGrid();

		$row = array('"'.$this->__('Period').'"');
		foreach ($this->_columns as $column) {
			if (!$column->getIsSystem()) {
				$row[] = '"'.$column->getHeader().'"';
			}
		}
		$csv.= implode(',', $row)."\n";

		$numColumns = sizeof($this->_columns);

		$_sizes = $this->getAllSizes();
		$_summaryData = $this->getSummaryData();
		$total = array();

		foreach ($_summaryData as $_period => $_data){
			foreach ($_sizes as $_size) {
				if ($_data[$_size]) {
					$row = array('"'.$_period.'"');
					$j=0;
	                foreach ($this->_columns as $_columnKey => $column) {
						$j ++;
						if (($j == $numColumns) || ($j == $numColumns - 1)) {
							$total[$_size][$_columnKey] += $_data[$_size][$_columnKey];
						} else {
							$total[$_size][$_columnKey] = $_data[$_size][$_columnKey];
						}

						if (!$column->getIsSystem()) {
							$row[] = '"' . str_replace(
								array('"', '\\'),
								array('""', '\\\\'),
								$_data[$_size][$_columnKey]
							) . '"';
						}
					}
					$csv.= implode(',', $row)."\n";
				}
			}
		}

		$_totalSold = array();
		foreach ($_sizes as $_size) {
			if ($total[$_size]) {
				$row = array('"'.$this->__('Total').'"');
				$j = 0;
				foreach ($this->_columns as $_columnKey => $column) {
					$j ++;
					if (($j == $numColumns) || ($j == $numColumns - 1)) {
						$_totalSold[$_columnKey] += $total[$_size][$_columnKey];
					}
					if (!$column->getIsSystem()) {
						$row[] = '"' . str_replace(
							array('"', '\\'),
							array('""', '\\\\'),
							$total[$_size][$_columnKey]
							) . '"';
					}
				}
				$csv.= implode(',', $row)."\n";
			}
		}

		$row = array();
		$row[] = '""';
		$row[] = '""';
		$row[] = $_totalSold['ordered_qty'];
		$row[] = $_totalSold['kg'];
		$csv.= implode(',', $row)."\n";

		return $csv;
	}
}
