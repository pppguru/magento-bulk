<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Components_Customergrid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     * Set sort settings
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sku_by_customer_grid');
        $this->setDefaultSort('first_name');
        $this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
		$this->_defaultLimit = 50;
    }
	
    /**
	 * @return Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Components_Customergrid_Grid
     */
    protected function _prepareCollection()
    {
		$sku = Mage::registry('product_sku');
		$fromDate = Mage::registry('from_date');
		$toDate = Mage::registry('to_date');
        $collection = Mage::helper('customreports')->getSkuByCustomerCollection($sku, $fromDate, $toDate);
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Add grid columns
     *
	 * @Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Components_Customergrid_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('first_name', array(
            'header'    => Mage::helper('customreports')->__('First Name'),
            'align'     =>'left',   
			'filter_index'=>'main_table.customer_firstname',         
            'index'     => 'first_name',
        ));
        $this->addColumn('last_name', array(
            'header'    => Mage::helper('customreports')->__('Last Name'),
            'align'     =>'left',
			'filter_index'=>'main_table.customer_lastname',
            'index'     => 'last_name',
        ));
		$this->addColumn('company', array(
            'header'    => Mage::helper('customreports')->__('Company'),
            'align'     =>'left',      
            'index'     => 'company',
        ));
		$this->addColumn('email', array(
            'header'    => Mage::helper('customreports')->__('Email'),
            'align'     =>'left',
			'filter_index'=>'main_table.customer_email',
            'index'     => 'email',
        ));
		$this->addColumn('telephone', array(
            'header'    => Mage::helper('customreports')->__('Telephone'),            
            'align'     => 'left',            
            'index'     => 'telephone'
        ));		
		$this->addColumn('street', array(
            'header'    => Mage::helper('customreports')->__('Street'),            
            'align'     => 'left',            
            'index'     => 'street'
        ));
        $this->addColumn('city', array(
            'header'    => Mage::helper('customreports')->__('City'),            
            'align'     => 'left',            
            'index'     => 'city'
        ));		
		$this->addColumn('region', array(
			'header'    => Mage::helper('customreports')->__('State'),            
            'align'     => 'left',            
			'index'     => 'region'
        ));
		$this->addColumn('zip', array(
            'header'    => Mage::helper('customreports')->__('Zip'),            
            'align'     => 'left',            
            'index'     => 'zip'
        ));
		$this->addColumn('order_id', array(
            'header'    => Mage::helper('customreports')->__('Order No.'),            
            'align'     => 'center',
			'filter_index'=>'main_table.entity_id',       
            'index'     => 'order_id'
        ));
		$this->addColumn('created_at', array(
            'header'    => Mage::helper('customreports')->__('Order Date'),            
            'align'     => 'center',            
            'index'     => 'created_at'
        ));
		$this->addColumn('sku', array(
			'header'    => Mage::helper('customreports')->__('SKU'),            
			'align'     => 'left',            
			'index'     => 'sku'
		));
		$this->addColumn('qty', array(
            'header'    => Mage::helper('customreports')->__('Qty'),            
            'align'     => 'center',
			'filter_index'=>'sfoi.qty_ordered',           
            'index'     => 'qty'
        ));
		//Add export CSV button to the grid
		$this->addExportType('*/*/exportCsv', Mage::helper('customreports')->__('CSV'));
        parent::_prepareColumns();
        return $this;		
    }
		
    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     *
     * @return false means nothing happens when a row is clicked
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
