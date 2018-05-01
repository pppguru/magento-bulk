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

if (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
$installer->run("

update {$this->getTable('dataflow_profile')}
set entity_type = null
where name in ('Export Product / Supplier associations',
                'import suppliers',
                'Import product / supplier association',
                'Export Products (purchase view)',
                'Export Supply Needs',
                'Export Contact',
                'Export Suppliers',
                'Export Purchase Order Products',
                'Export Purchase Order',
                'Export Products Stock Movements'
                );

");
}

$installer->endSetup();
