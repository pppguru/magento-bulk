<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnStatusInquiry extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $html = '';

        $date_expl = explode('-', $row->getrsr_status_set_to_inquiry_at());
        if (count($date_expl) > 1) {
            $date_expl[2] = substr($date_expl[2], 0, -9);
            $html .= $date_expl['2'] . '/' . $date_expl['1'] . '/' . $date_expl['0'];

            $date            = strtotime($row->getrsr_status_set_to_inquiry_at());
            $date_to_compare = time() - 2592000; //there is one month
            $between_date    = $date - $date_to_compare;
            if ($between_date > 2592000)
                $html = '<font color="red">' . $html . '</font>';
        }

        return $html;
    }
}

