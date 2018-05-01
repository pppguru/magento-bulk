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
class MDN_Purchase_Block_Manufacturer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ManufacturerGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    /**
     * Load manufacturers
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $collection = Mage::getModel('Purchase/Manufacturer')
        	->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * grid columns
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
     
        $this->addColumn('Id', array(
            'header'=> Mage::helper('purchase')->__('Id'),
            'index' => 'man_id',
        ));
                          
        $this->addColumn('Name', array(
            'header'=> Mage::helper('purchase')->__('Name'),
            'index' => 'man_name',
        ));
        
        $this->addColumn('Contact', array(
            'header'=> Mage::helper('purchase')->__('Contact'),
            'index' => 'man_contact',
        ));

        $this->addColumn('Phone', array(
            'header'=> Mage::helper('purchase')->__('Phone'),
            'index' => 'man_tel',
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
     * Definir l'url pour chaque ligne
     * permet d'acceder a l'ecran "d'edition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Manufacturers/Edit', array())."man_id/".$row->getId();
    }
    
    /**
     * Url pour ajouter un Custom Shipping
     *
     */
    public function getNewUrl()
    {
		return $this->getUrl('adminhtml/Purchase_Manufacturers/New', array());
    }
    
}
