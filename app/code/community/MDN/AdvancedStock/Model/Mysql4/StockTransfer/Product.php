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
class MDN_AdvancedStock_Model_Mysql4_StockTransfer_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('AdvancedStock/StockTransfer_Product', 'stp_id');
    }

    public function getRemainingToTransferSum($transferId)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('read');
        $select = $read->select()
            ->from(array('tbl_transfer_product'=>$this->getMainTable()),
                array('SUM(stp_qty_requested - stp_qty_transfered) AS remaining_to_tranfer'))
            ->where('stp_transfer_id = ?', $transferId);

        $value = $read->fetchOne($select);
        return $value;
    }

    public function getTransferedSum($transferId)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('read');
        $select = $read->select()
            ->from(array('tbl_transfer_product'=>$this->getMainTable()),
                array('SUM(stp_qty_transfered) AS remaining_to_tranfer'))
            ->where('stp_transfer_id = ?', $transferId);

        $value = $read->fetchOne($select);
        return $value;
    }

}
?>