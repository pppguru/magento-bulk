<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_Entity_Source_Accesscontrol extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    const USE_CONFIG = 'use_config';
    /**
     * {@inheritDoc}
     */
    public function getAllOptions()
    {
        $accessControl = Mage::getModel('rpsc/system_config_source_accesscontrol')
            ->toOptionArray();
        return array_merge(array(
            array(
                'value' => self::USE_CONFIG,
                'label' => 'Use config settings'
            ),
        ), $accessControl);
    }

    /**
     * {@inheritDoc}
     */
    public function getFlatColums()
	{
		return array($this->getAttribute()->getAttributeCode() => array(
            'type'      => 'varchar(255)',
            'unsigned'  => false,
            'is_null'   => true,
            'default'   => null,
            'extra'     => null
		));
	}

    /**
     * {@inheritDoc}
     */
    public function getFlatUpdateSelect($store)
    {
		$attribute = $this->getAttribute();
        $joinCondition = "`e`.`entity_id`=`t1`.`entity_id`";
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= " AND `e`.`child_id`=`t1`.`entity_id`";
        }
        $select = $attribute->getResource()->getReadConnection()->select()
            ->joinLeft(
                array('t1' => $attribute->getBackend()->getTable()),
                $joinCondition,
                array()
                )
            ->joinLeft(
                array('t2' => $attribute->getBackend()->getTable()),
                "t2.entity_id = t1.entity_id"
                    . " AND t1.entity_type_id = t2.entity_type_id"
                    . " AND t1.attribute_id = t2.attribute_id"
                    . " AND t2.store_id = {$store}",
                array($attribute->getAttributeCode() => "IFNULL(t2.value, t1.value)"))
            ->where("t1.entity_type_id=?", $attribute->getEntityTypeId())
            ->where("t1.attribute_id=?", $attribute->getId())
            ->where("t1.store_id=?", 0);
        if ($attribute->getFlatAddChildData()) {
            $select->where("e.is_child=?", 0);
        }
        return $select;
    }
}
