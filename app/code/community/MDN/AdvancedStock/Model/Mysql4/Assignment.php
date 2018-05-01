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
class MDN_AdvancedStock_Model_Mysql4_Assignment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('AdvancedStock/Assignment', 'csa_id');
    }
    
    /**
     * 
     *
     */
    public function deleteAssignmentsForStock($stockId)
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "csa_stock_id=".$stockId);
    	return $this;
    }
    
}
?>