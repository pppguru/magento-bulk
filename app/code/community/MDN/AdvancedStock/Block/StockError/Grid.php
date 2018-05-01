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
class MDN_AdvancedStock_Block_StockError_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Enter description here...
	 *
	 */
    public function __construct()
    {
        parent::__construct();
        $this->setId('StockErrorGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		$collection = Mage::getModel('AdvancedStock/StockError')
					->getCollection()
					->join('cataloginventory/stock_item','se_stock_id=item_id')
					->join('catalog/product','`catalog/product`.entity_id=se_product_id')
					->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and store_id = 0 and attribute_id = '.mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId())
					;
                 
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
    	$this->addColumn('sku', array(
            'header'=> Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Product'),
            'index' => 'value'
        ));

        $this->addColumn('stock_id', array(
            'header'    => Mage::helper('AdvancedStock')->__('Warehouse'),
            'width'     => '80',
            'index'     => 'stock_id',
            'type'  => 'options',
            'options' => Mage::getSingleton('AdvancedStock/System_Config_Source_Warehouse')->getListForFilter(),
        ));
        
        $this->addColumn('se_comments', array(
            'header'=> Mage::helper('AdvancedStock')->__('Comments'),
            'index' => 'se_comments'
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('AdvancedStock')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getse_id',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('AdvancedStock')->__('Fix error'),
                        'url'     => array('base'=>'AdvancedStock/Misc/FixError'),
                        'field'   => 'se_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'se_stock_id',
                'is_system' => true,
        ));
        
        return parent::_prepareColumns();
    }


    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
    	return $this->getUrl('AdvancedStock/Products/Edit', array('product_id' => $row->getse_product_id()));
    }

}
