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
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_BackgroundTask_Block_Widget_Grid_Column_Renderer_Results extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $status = $row->getbt_result();
        $html = $status;

        if ($status) {
            switch ($status) {
                case MDN_BackgroundTask_Helper_Data::RESULT_SUCCESS:
                    $color = 'green';
                    break;
                case MDN_BackgroundTask_Helper_Data::RESULT_ERROR:
                    $color = 'red';
                    break;
            }
            $html = '<font color="' . $color . '">' . $status . '</font>';
        }

        return $html;
    }

}
