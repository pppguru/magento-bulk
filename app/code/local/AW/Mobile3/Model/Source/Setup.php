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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile3_Model_Source_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
{
    public function processMigration()
    {
        try {
            $this->_copyMobileProductDescription();
            $this->_copyMobileSettings();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _copyMobileProductDescription()
    {
        $oldAttributeId = $this->getAttributeId('catalog_product', 'mobile_description');
        if ($oldAttributeId) {
            $newAttributeId = $this->getAttributeId('catalog_product', AW_Mobile3_Helper_Data::PRODUCT_DESCRIPTION_MOBILE_ATTRIBUTE);
            $adapter = $this->getConnection();
            $select = $adapter->select()->from($this->getTable('catalog/product') . '_text',
                array('entity_type_id', new Zend_Db_Expr($newAttributeId), 'store_id', 'entity_id', 'value')
            );
            $select->where('attribute_id =?', $oldAttributeId);

            $fields = array(
                'entity_type_id',
                'attribute_id',
                'store_id',
                'entity_id',
                'value',
            );
            $query = $select->insertFromSelect($this->getTable('catalog/product') . '_text', $fields);
            $this->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE)->query($query);
        }
        return $this;
    }

    protected function _copyMobileSettings()
    {
        $mobileConfigPaths = array(
            'awmobile/design/logo_src',
            'awmobile/design/bootmarks_src',
            'awmobile/behaviour/switcher',
        );
        $mobile3ConfigPaths = array(
            array(
                AW_Mobile3_Helper_Config::DESIGN_TABLET_LOGO_SRC,
                AW_Mobile3_Helper_Config::DESIGN_MOBILE_LOGO_SRC
            ),
            array(AW_Mobile3_Helper_Config::DESIGN_BOOKMARKS_SRC),
            array(AW_Mobile3_Helper_Config::BEHAVIOR_SWITCHER)
        );
        $adapter = $this->getConnection();
        foreach ($mobile3ConfigPaths as $key => $newConfigPath) {
            foreach ($newConfigPath as $path) {
                $select = $adapter->select()->from($this->getTable('core/config_data'),
                    array('scope', 'scope_id', new Zend_Db_Expr('"' . $path . '"'), 'value')
                );
                $select->where('path =?', $mobileConfigPaths[$key]);
                $fields = array(
                    'scope',
                    'scope_id',
                    'path',
                    'value'
                );
                $query = $select->insertFromSelect($this->getTable('core/config_data'), $fields);
                $this->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE)->query($query);
            }
        }

        //replace images
        $this->_replaceFile(Mage::getBaseDir('media') . '/awmobile', Mage::getBaseDir('media') . '/aw_mobile3');
        return $this;
    }

    protected function _replaceFile($source, $dest)
    {
        if (is_dir($source)) {
            $this->_prepareFolder($dest);
            foreach(glob($source . '/*') as $file) {
                $this->_replaceFile($source . '/' . basename($file), $dest . '/' . basename($file));
            }
        } else {
            @copy($source, $dest);
        }
        return $this;
    }

    protected function _prepareFolder($folderPath)
    {
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0775);
        }
        return $this;
    }
}