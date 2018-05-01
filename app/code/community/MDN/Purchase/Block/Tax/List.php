<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Block_Tax_List extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('TaxGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge
        $collection = Mage::getModel('Purchase/TaxRates')
        	->getCollection()
        	;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
                               
        $this->addColumn('Id', array(
            'header'=> Mage::helper('sales')->__('Id'),
            'index' => 'ptr_id'
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('sales')->__('Name'),
            'index' => 'ptr_name',
        ));
        
        $this->addColumn('Rate', array(
            'header'=> $this->__('Rate'),
            'index' => 'ptr_value',
        ));

        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Tax/Edit', array('ptr_id' => $row->getId()));
    }
    
    /**
     * Retourne l'url pour ajouter un nouveau taux de taxe
     *
     */
    public function getNewUrl()
    {
    	return $this->getUrl('adminhtml/Purchase_Tax/New');
    }
    
}
