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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_DeliveryDate extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row) {

        $value = $row->getpop_delivery_date();
        $onclick = ' onchange="persistantProductGrid.logChange(this.name, \''.$value.'\')" ';
        $name = 'pop_delivery_date_'.$row->getpop_num();
        
        $html = '';
        $html .= '<input '.$onclick.' type="text" size="7" name="'.$name.'" id="'.$name.'" value="'.$value.'">';

        $html .= '<img src="'.$this->getSkinUrl('images/grid-cal.gif').'" class="v-middle" id="img_calendar_date_'.$name.'" />
                <script type="text/javascript">
                Calendar.setup({
                    inputField : \''.$name.'\',
                    ifFormat : \'%Y-%m-%e\',
                    button : \'img_calendar_date_'.$name.'\',
                    align : \'Bl\',
                    singleClick : true
                });
        </script>';


        return $html;
    }

}