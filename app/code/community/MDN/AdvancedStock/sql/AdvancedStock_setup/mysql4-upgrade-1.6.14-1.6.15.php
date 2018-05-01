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

//Force magento stock management settings
$path = 'cataloginventory/options/can_subtract';
$obj = new Mage_Core_Model_Config();
$obj ->saveConfig($path, 0);

$path = 'cataloginventory/options/can_back_in_stock';
$obj = new Mage_Core_Model_Config();
$obj ->saveConfig($path, 0);

$installer->endSetup();
