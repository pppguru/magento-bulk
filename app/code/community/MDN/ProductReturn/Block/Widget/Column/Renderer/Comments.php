<?php


class MDN_ProductReturn_Block_Widget_Column_Renderer_Comments
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //init vars
        $comments = $row->getAllCommentsMerged();
        $max      = 255;
        if (strlen($comments) < 255)
            $max = strlen($comments) - 1;
        $summarize = substr($comments, 0, $max);

        //build html return
        $html = '<span class="">' . $this->stripTags($summarize, '<br>') . '</span>';

        return $html;
    }
}
