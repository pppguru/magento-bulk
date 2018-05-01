<?php
class Bulksupplements_CustomOrder_Model_Sales_Order extends Mage_Sales_Model_Order {
    public function hasCustomFields()
    {
        $var = $this->getPonumber();
        if ($var && !empty($var)) {
            return true;
        } else {
            return false;
        }
    }

    public function getFieldHtml() {
        $var = $this->getPonumber();
        $html = '';
        if ($var && !empty($var)) {
            $html = 'PO Number: '.$var.'<br/>';
        }
        return $html;
    }

    public function getCustomVars() {
        $model = Mage::getModel('customorder/customorder_order');
        return $model->getByOrder($this->getId());
    }
}