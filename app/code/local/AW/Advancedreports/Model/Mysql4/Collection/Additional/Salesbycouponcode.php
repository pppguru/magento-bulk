<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_ARUnits/Salesbycouponcode
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbycouponcode
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{

    /**
     * Add Items
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbycouponcode
     */
    public function addOrderItemsCount()
    {
        $filterField = $this->_helper()->confOrderDateFilter();
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                   ->columns(array('qty_ordered_count'=>'total_qty_ordered'))
                   ;
        } else {
            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
            $this->getSelect()
                    ->joinRight( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)", array('qty_ordered_count'=>'COUNT(qty_ordered)'))
                    ->order("e.{$filterField} DESC")
                    ->group('e.entity_id')
                    ;
        }
        return $this;
    }

    /**
     * Add coupon code
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbycouponcode
     */
    public function addCouponCode()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $this->getSelect()->columns('coupon_code');
        } else {
            $eavAttribute = $this->_helper()->getSql()->getTable('eav_attribute');
            $salesValue = $this->_helper()->getSql()->getTable('sales_order_varchar');
            $this->getSelect()
                ->joinLeft(array( 'coup_attr' => $eavAttribute ), "coup_attr.attribute_code = 'coupon_code' AND coup_attr.entity_type_id = e.entity_type_id", array())
                ->joinLeft(array( 'coup_value' => $salesValue ), "coup_attr.attribute_id = coup_value.attribute_id AND coup_value.entity_type_id = e.entity_type_id AND coup_value.entity_id = e.entity_id", array( 'coupon_code' => 'value' ))
            ;
        }
        return $this;
    }

}
