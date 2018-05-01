<?php

class MDN_Purchase_Model_Convert_Adapter_SupplyNeeds extends Mage_Dataflow_Model_Convert_Container_Abstract
{
	private $_collection = null;
	const k_lineReturn = "\r\n";
	
	 /**
     * Load product collection Id(s)
     *
     */
    public function load()
    {
    	//Charge les commandes fournisseur
		$this->_collection = mage::getModel('Purchase/SupplyNeedsWarehouse')
			->getCollection()
			->setOrder('product_id', 'asc');

		//Affiche le nombre de commande charg�e
		$this->addException(Mage::helper('dataflow')->__('Loaded %s rows', $this->_collection->getSize()), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }
    
    /**
     * Enregistre
     *
     */
    public function save()
    {
    	$this->load();
    	
    	//D�finit le chemin ou sauver le fichier
    	$path = $this->getVar('path').'/'.$this->getVar('filename');
    	$f = fopen($path, 'w');
    	$fields = $this->getFields();

    	//add header
    	$header = '';
    	foreach($fields as $field)
    	{
    		$header .= $field.';';
    	}
    	fwrite($f, $header.self::k_lineReturn );
    	
    	//add orders
    	foreach($this->_collection as $item)
    	{
    		$line = '';
	    	foreach($fields as $field)
	    	{
	    		$fieldValue = $item->getData($field);
	    		$fieldValue = str_replace("\n", ', ', $fieldValue);
	    		$fieldValue = strip_tags($fieldValue);
	    		$line .= $fieldValue.';';
	    	}    		
	    	fwrite($f, $line.self::k_lineReturn );    	
    	}
    	
		//Affiche le nombre de commande charg�e
		fclose($f);
		$this->addException(Mage::helper('dataflow')->__('Export saved in %s', $path), Mage_Dataflow_Model_Convert_Exception::NOTICE);

    }
    
    /**
     * return fields to export
     *
     */
    public function getFields()
    {
    	$t = array();
    	$t = explode(';', $this->getVar('fields'));
    	return $t;
    }

}