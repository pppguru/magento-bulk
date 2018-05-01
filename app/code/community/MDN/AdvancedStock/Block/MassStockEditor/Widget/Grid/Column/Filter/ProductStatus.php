<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Filter_ProductStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract {


    public function getCondition() {

        $productIds = array();

        $valueSelected = '';

        $value = $this->getValue();

        if($value && is_array($value)){
            if(array_key_exists('status', $value))
                $valueSelected = $value['status'];
        }
        $filterApplied = false;

        if($valueSelected){
            $statusAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'status')->getId();

            //Select the stock managed products depending of the filter values
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'SELECT tbl_stock_item.item_id ';
            $sql .= 'FROM '.$prefix.'cataloginventory_stock_item tbl_stock_item ';
            $sql .= 'LEFT JOIN '.$prefix.'catalog_product_entity tbl_product ';
            $sql .= 'ON tbl_stock_item.product_id = tbl_product.entity_id ';
            $sql .= 'LEFT JOIN '.$prefix.'catalog_product_entity_int tbl_ent_int ';
            $sql .= 'ON tbl_ent_int.entity_id = tbl_product.entity_id ';
            $sql .= 'WHERE tbl_ent_int.store_id = 0 ';
            $sql .= 'AND tbl_ent_int.attribute_id = '.$statusAttributeId.' ';
            $sql .= 'AND tbl_ent_int.value = '.$valueSelected.' ;';

            $productIds = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);
            $filterApplied = true;
        }
        

        if (count($productIds) > 0 || $filterApplied)
          return array('in' => $productIds);
        else
          return null;
    }

    /**
     * Display the filter html including
     *  - the from filter
     *  - the to filter
     * @return string
     */
    public function getHtml() {

        $html = '';

        $valueSelected = '';

        $value = $this->getValue();

        if($value && is_array($value)){
            if(array_key_exists('status', $value))
              $valueSelected = $value['status'];
        }

        $html = '<select  name="'.$this->_getHtmlName().'[status]" id="'.$this->_getHtmlId().'_status">';

            $html .= '<option value="" ></option>';
            
            $html .= '<option value="1" ' . (($valueSelected == "1")?'selected':'') . '>'.Mage::helper('AdvancedStock')->__('Enabled').'</option>';

            $html .= '<option value="2" ' . (($valueSelected == "2")?'selected':'') . '>'.Mage::helper('AdvancedStock')->__('Disabled').'</option>';

        $html .= '</select>';


    return $html;
    }

    /**
     * Allow the values defined in the custom HTML filter
     *
     * @param type $index
     * @return null
     */
    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if (isset($value['status']) && strlen($value['status']) > 0) {
            return $value;
        }
        return null;
    }

}