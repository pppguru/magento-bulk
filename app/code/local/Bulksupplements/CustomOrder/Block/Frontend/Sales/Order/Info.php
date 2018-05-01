<?php

class Bulksupplements_CustomOrder_Block_Frontend_Sales_Order_Info extends Mage_Sales_Block_Order_Info {
	public function getCustomVars() {
		$model = Mage::getModel('customorder/customorder_order');
		return $model->getByOrder($this->getOrder()->getId());
	}
}