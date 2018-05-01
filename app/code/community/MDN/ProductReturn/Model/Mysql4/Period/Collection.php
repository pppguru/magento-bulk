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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Collection de quotation
 *
 */
class MDN_ProductReturn_Model_Mysql4_Period_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/Period');
    }

    /**
     * return query for product report
     *
     * @return MDN_ProductReturn_Model_Mysql4_Period_Collection
     */
    public function getProductReport()
    {
        $this->join('rma', 'rma_created_at >= rrp_from and rma_created_at <= rrp_to', '');
        $this->join('rma_products', 'rma_id = rp_rma_id', array('rp_product_id', 'rp_product_name'));
        $this->join('product_entity', 'entity_id = rp_product_id', 'sku');

        $this->addFieldToFilter('rp_action', array('neq' => ''));

        $this->addExpressionFieldToSelect('qty', 'sum(rp_qty)', 'qty');

        //change PK value to avoid duplicate PK issue in grid !!
        $this->addExpressionFieldToSelect('rrp_id', 'concat(rrp_id,\'_\',rp_product_id)', array('rrp_id', 'rp_product_id'));

        $this->getSelect()->group(array('rp_product_id', 'sku', 'rrp_id', 'rrp_name', 'rrp_from', 'rrp_to'));

        
        return $this;
    }

    /**
     * Return query for reason
     */
    public function getReasonReport()
    {
        $this->join('rma', 'rma_created_at >= rrp_from and rma_created_at <= rrp_to', '');
        $this->join('rma_products', 'rma_id = rp_rma_id', array('rp_product_id', 'rp_product_name'));

        $this->addFieldToFilter('rp_action', array('neq' => ''));

        $this->addExpressionFieldToSelect('qty', 'sum(rp_qty)', 'qty');

        //change PK value to avoid duplicate PK issue in grid !!
        $this->addExpressionFieldToSelect('rrp_id', 'concat(rrp_id,\'_\',rp_product_id)', array('rrp_id', 'rp_product_id'));

        $this->getSelect()->group(array('rrp_id', 'rrp_name', 'rrp_from', 'rrp_to'));

        return $this;

    }


}