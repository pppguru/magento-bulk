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
class MDN_Purchase_Model_Packaging  extends Mage_Core_Model_Abstract
{
    const DEFAULT_FOR_SALES_FIELD = 'pp_is_default_sales';
    const DEFAULT_FOR_PURCHASE_FIELD = 'pp_is_default';

	public function _construct()
	{
		parent::_construct();
		$this->_init('Purchase/Packaging');
	}

    public function convertUnitToPackage($qty)
    {
        return ceil($qty / $this->getpp_qty());
    }


    protected function _afterSave() {
        parent::_afterSave();


        //garanty Unicity for default for purchase and default for sales on any save
        if (($this->getOrigData(self::DEFAULT_FOR_PURCHASE_FIELD) != $this->getData(self::DEFAULT_FOR_PURCHASE_FIELD))
                && $this->getData(self::DEFAULT_FOR_PURCHASE_FIELD) == 1){
            $this->updateUniqueDefaultForPurchase();
        }

        if (($this->getOrigData(self::DEFAULT_FOR_SALES_FIELD) != $this->getData(self::DEFAULT_FOR_SALES_FIELD))
                && $this->getData(self::DEFAULT_FOR_SALES_FIELD) == 1){
            $this->updateUniqueDefaultForSales();
        }
    }

    private function updateUniqueDefaultForSales(){
         $this->updateUniqueDefaultValue(self::DEFAULT_FOR_SALES_FIELD);
    }

    private function updateUniqueDefaultForPurchase(){
         $this->updateUniqueDefaultValue(self::DEFAULT_FOR_PURCHASE_FIELD);
    }

    private function updateUniqueDefaultValue($field){
        $id = $this->getpp_id();
        $pid = $this->getpp_product_id();

        $conn = mage::getResourceModel('sales/order_item_collection')->getConnection();
        $table = mage::getModel('Purchase/Constant')->getTablePrefix().'purchase_packaging';

        $sql = 'UPDATE '.$table.' SET  '.$field.' = 0 WHERE pp_product_id = '.$pid;
        $conn->query($sql);

        $sql = 'UPDATE '.$table.' SET  '.$field.' = 1 WHERE pp_id = '.$id;
        $conn->query($sql);

    }
}