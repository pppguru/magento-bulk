<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$setup = $this;
$setup->startSetup();

$attributeApdater = new Mage_Eav_Model_Entity_Setup('core_setup');

$attributeApdater->updateAttribute(
                       'catalog_product',
                       AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE,
                       'frontend_model',
                       'catalogpermissions/product_attribute_frontend_hideprice'
                    );

$setup->endSetup();
