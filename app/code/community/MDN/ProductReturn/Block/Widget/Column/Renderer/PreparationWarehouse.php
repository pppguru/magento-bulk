<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_PreparationWarehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $websiteId = $row->getwebsite_id();
        $warehouse = mage::helper('AdvancedStock/Warehouse')->getWarehouseForAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentOrderPreparation);

        return $warehouse->getstock_name();
    }
}