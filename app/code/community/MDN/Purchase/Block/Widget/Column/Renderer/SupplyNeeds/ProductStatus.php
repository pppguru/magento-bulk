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
 * @copyright  Copyright (c) 2016 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_ProductStatus
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        return $this->getProductStatus($row,true);    }

    public function renderExport(Varien_Object $row) {
        return $this->getProductStatus($row,false);
    }

    private function getProductStatus($row,$useHtml = true){
        $labels = Mage_Catalog_Model_Product_Status::getOptionArray();
        return $labels[$row->getstatus()];
    }


}