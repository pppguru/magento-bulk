<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Wsaolddominion
 * User         Genevieve Eddison
 * Date         19 May 2013
 * Time         09:00
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
class Webshopapps_Wsaolddominion_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup {

    public function getDefaultEntities() {
        return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'attributes'        => array(
                    'freight_class_select' => array(
                        'group'             => 'Shipping',
                        'type'              => 'varchar',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Freight Class',
                        'input'             => 'select',
                        'class'             => '',
                        'source'            => 'wsaolddominion/carrier_wsaolddominion_source_freightclass',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => true,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                        'apply_to'          => 'simple,configurable,bundle,grouped',
                    )
                )
            )
        );
    }
}
?>