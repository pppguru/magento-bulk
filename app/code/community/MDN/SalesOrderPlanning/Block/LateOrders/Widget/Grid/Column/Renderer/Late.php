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

class MDN_SalesOrderPlanning_Block_LateOrders_Widget_Grid_Column_Renderer_Late extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$diffInDays = 0;
		$color = 'black';

		$dayInSeconds = 3600 * 24;
		$currentDate = strtotime("now");

		$announcedDate = strtotime($row->getanounced_date());
		$planningDate = strtotime($row->getpsop_delivery_date());
		$expectedDeliveryDate = ($announcedDate)?$announcedDate:$planningDate;

		if (!empty($currentDate) && !empty($expectedDeliveryDate)) {

			$differenceInSeconds = ($currentDate - $expectedDeliveryDate);
			if($differenceInSeconds >= $dayInSeconds) {
				$diffInDays = round(($differenceInSeconds / $dayInSeconds),0);

				$color = 'red';

				if ($diffInDays < 0)
					$color = 'green';

				if ($diffInDays < 3)
					$color = 'orange';
			}
		}

		return '<font color="'.$color.'">'.$diffInDays.'</font>';
	}

}