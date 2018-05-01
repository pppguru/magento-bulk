<?php

class MDN_Purchase_Model_Convert_Adapter_Suppliers extends Mage_Dataflow_Model_Convert_Container_Abstract {

    private $_collection = null;

    const k_lineReturn = "\r\n";
    const k_lineFeed = "\n";
    const k_CarriageReturn = "\r";
    const k_separator = ";";

    /**
     * Load product collection Id(s)
     *
     */
    public function load() {
        //Charge les commandes fournisseur
        $this->_collection = mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_id', 'asc');

        //Affiche le nombre de commande chargee
        $this->addException(Mage::helper('dataflow')->__('Loaded %s rows', $this->_collection->getSize()), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }

    /**
     * Enregistre
     *
     */
    public function save() {
        $this->load();

        //Definit le chemin ou sauver le fichier
        $path = $this->getVar('path') . '/' . $this->getVar('filename');
        $f = fopen($path, 'w');
        $fields = $this->getFields();

        //add header
        $header = '';
        foreach ($fields as $field) {
            $field = trim(str_replace(self::k_separator,' ', $field));
            $field = trim(str_replace(self::k_lineReturn,' ', $field));
            $field = trim(str_replace(self::k_lineFeed,' ', $field));
            $field = trim(str_replace(self::k_CarriageReturn,' ', $field));
            $header .= $field . self::k_separator;
        }
        fwrite($f, $header . self::k_lineReturn);

        //add suppliers
        foreach ($this->_collection as $item) {
            $line = '';
            foreach ($fields as $field) {
                $field = trim(str_replace(self::k_separator,' ', $item->getData($field)));
                $field = trim(str_replace(self::k_lineReturn,' ', $field));
                $field = trim(str_replace(self::k_lineFeed,' ', $field));
                $field = trim(str_replace(self::k_CarriageReturn,' ', $field));
                $line .=  $field. self::k_separator;
            }
            fwrite($f, $line . self::k_lineReturn);
        }

        //Affiche le nombre de commande chargee
        fclose($f);
        $this->addException(Mage::helper('dataflow')->__('Export saved in %s', $path), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }

    /**
     * return fields to export
     *
     */
    public function getFields() {
        $t = array();
        $t = explode(self::k_separator, $this->getVar('fields'));
        return $t;
    }
}

