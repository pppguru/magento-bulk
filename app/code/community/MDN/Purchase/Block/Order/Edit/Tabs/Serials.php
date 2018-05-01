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
class MDN_Purchase_Block_Order_Edit_Tabs_Serials extends Mage_Adminhtml_Block_Widget_Grid
{
	private $_order = null;
	
	/**
	 * D�finit l'order
	 *
	 */
	public function setOrderId($value)
	{
		$this->_order = mage::getModel('Purchase/Order')->load($value);
		return $this;
	}
	
	/**
	 * Retourne la commande
	 *
	 */
	public function getOrder()
	{
		return $this->_order;
	}
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductSelection');
        $this->setUseAjax(true);
        $this->setEmptyText($this->__('No items'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		$collection = Mage::getModel('AdvancedStock/ProductSerial')
					->getCollection()
					->join('catalog/product', 'pps_product_id=entity_id')
			        ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and store_id = 0 and attribute_id = '.mage::getModel('Purchase/Constant')->GetProductNameAttributeId())
			        ->addFieldToFilter('pps_purchaseorder_id', $this->getOrder()->getId());
        	
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
	     $this->addColumn('sku', array(
            'header'=> Mage::helper('purchase')->__('Sku'),
            'index' => 'sku'
        ));
        
	     $this->addColumn('value', array(
            'header'=> Mage::helper('purchase')->__('Product'),
            'index' => 'value'
        ));
        
        $this->addColumn('pps_serial', array(
            'header'=> Mage::helper('purchase')->__('Serial'),
            'index' => 'pps_serial',
            'align' => 'center'
        ));
        
                     
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/Purchase_Orders/ProductSerialsGrid', array('_current'=>true, 'po_num' => $this->getOrder()->getId()));
    }

}
