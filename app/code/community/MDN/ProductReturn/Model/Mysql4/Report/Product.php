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
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_Mysql4_Report_Product extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {

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

        $this->addOrder('rp_product_name');
        $this->addOrder('rrp_from');

        $this->getSelect()->group(array('rp_product_id', 'rp_product_name', 'sku'));

        return $this;
    }

}