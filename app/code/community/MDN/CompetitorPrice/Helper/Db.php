<?php

class MDN_CompetitorPrice_Helper_Db extends Mage_Core_Helper_Abstract {

    public function createTable()
    {
        $prefix = Mage::getConfig()->getTablePrefix();

        $sql = "DROP TABLE IF EXISTS `".$prefix."bms_competitor_price_product`;";
        Mage::getResourceModel('CompetitorPrice/Product_Collection')->getConnection()->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `".$prefix."bms_competitor_price_product` (
                   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id' ,
                  `product_id` int(11) NOT NULL COMMENT 'Product ID',
                  `channel` varchar(30) NOT NULL COMMENT 'Channel',
                  `last_update` timestamp NULL DEFAULT NULL COMMENT 'Last update',
                  `details` text NOT NULL COMMENT 'Offers',
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='bms_competitor_price_product';";

        Mage::getResourceModel('CompetitorPrice/Product_Collection')->getConnection()->query($sql);

    }

}





