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


class MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_NeededQty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        return $this->getNeededQty($row,true);    }

    public function renderExport(Varien_Object $row) {
        return $this->getNeededQty($row,false);
    }

    private function getNeededQty($row,$useHtml = true){
        $buffer = '';
        $productId = $row->getproduct_id();
        $idealStockLevel = mage::helper('AdvancedStock/Product_PreferedStockLevel')->getIdealStockLevelForAllStocks($productId);

        if($useHtml){
            $buffer = '<span style="white-space: nowrap;">';
            $buffer .= '<b>'.$this->__('Min : %s', (int)$row->getqty_min()).'</b>';
            $buffer .= ' - '.$this->__('Max : %s', (int)$row->getqty_max());
            $buffer .= '<br><i>('.$idealStockLevel.')</i>';
            $buffer .= '</span>';
        }else{
            $buffer .= 'Min : '.(int)$row->getqty_min().' Max : '.(int)$row->getqty_max();
        }

        return $buffer;
    }
}