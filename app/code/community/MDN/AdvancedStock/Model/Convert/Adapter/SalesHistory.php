<?php

class MDN_AdvancedStock_Model_Convert_Adapter_SalesHistory extends Mage_Dataflow_Model_Convert_Container_Abstract
{
	private $_collection = null;
	const k_lineReturn = "\r\n";
	
	 /**
     * Load product collection Id(s)
     *
     */
    public function load()
    {
		$this->_collection = mage::getModel('AdvancedStock/SalesHistory')    
			->getCollection()
			->join('cataloginventory/stock', 'sh_stock_id=stock_id')
			->join('catalog/product', 'sh_product_id=entity_id')
			->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and `AdvancedStock/CatalogProductVarchar`.store_id = 0 and `AdvancedStock/CatalogProductVarchar`.attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId())
        	;	
		
		$this->addException(Mage::helper('dataflow')->__('Loaded %s rows', $this->_collection->getSize()), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }
    
    /**
     * Enregistre
     *
     */
    public function save()
    {
    	$this->load();
    	
    	$path = $this->getVar('path').'/'.$this->getVar('filename');
    	$f = fopen($path, 'w');
    	
    	//add header
    	$header = 'warehouse;sku;name;update_date';
		$ranges = mage::helper('AdvancedStock/Sales_History')->getRanges();
		foreach($ranges as $range)
		{
			$header .= 'last '.$range.' weeks;';
		}
		
    	fwrite($f, $header.self::k_lineReturn );
    	
    	//add
    	foreach($this->_collection as $item)
    	{
    		$line = '';
			$line .= $item->getStockName().';';
			$line .= $item->getSku().';';
	    	$line .= $item->getvalue().';';    		
	    	$line .= $item->getsh_updated_at().';';    		
	    	$line .= $item->getsh_period_1().';';    		
	    	$line .= $item->getsh_period_2().';';    		
	    	$line .= $item->getsh_period_3().';';    		
	    	fwrite($f, $line.self::k_lineReturn );    	
    	}

		fclose($f);
		$this->addException(Mage::helper('dataflow')->__('Export saved in %s', $path), Mage_Dataflow_Model_Convert_Exception::NOTICE);

    }

}