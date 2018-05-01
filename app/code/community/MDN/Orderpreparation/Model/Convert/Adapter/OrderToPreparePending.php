<?php

class MDN_Orderpreparation_Model_Convert_Adapter_OrderToPreparePending extends Mage_Dataflow_Model_Convert_Container_Abstract
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
		$this->_collection = mage::getModel('Orderpreparation/ordertopreparepending')    
			->getCollection()
			->join('sales/order', 'opp_order_id=entity_id');	

		//Affiche le nombre de commande chargée
		$this->addException(Mage::helper('dataflow')->__('Loaded %s rows', $this->_collection->getSize()), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }
    
    /**
     * Enregistre
     *
     */
    public function save()
    {
    	$this->load();
    	
    	//Définit le chemin ou sauver le fichier
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
	    		$fieldValue = str_replace("<br>", ', ', $fieldValue);
	    		$fieldValue = strip_tags($fieldValue);
	    		$line .= $fieldValue.';';
	    	}    		
	    	fwrite($f, $line.self::k_lineReturn );    	
    	}
    	
		//Affiche le nombre de commande chargée
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