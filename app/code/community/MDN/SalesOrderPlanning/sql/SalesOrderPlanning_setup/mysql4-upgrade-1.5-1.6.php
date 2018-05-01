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
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("	

CREATE TABLE  {$this->getTable('product_availability')}  (
`pa_id` INT NOT NULL AUTO_INCREMENT ,
`pa_product_id` INT NOT NULL ,
`pa_website_id` INT NOT NULL ,
`pa_available_qty` INT NOT NULL ,
`pa_supply_delay` TINYINT NOT NULL ,
`pa_allow_backorders` TINYINT NOT NULL ,
`pa_has_outofstock_period` TINYINT NOT NULL ,
`pa_outofstock_start` DATE NULL ,
`pa_outofstock_end` DATE NULL ,
`pa_is_saleable` TINYINT NOT NULL ,
`pa_backinstock_date` DATE NULL ,
PRIMARY KEY (  `pa_id` ) ,
INDEX (  `pa_product_id` ,  `pa_website_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

	
$installer->endSetup();
