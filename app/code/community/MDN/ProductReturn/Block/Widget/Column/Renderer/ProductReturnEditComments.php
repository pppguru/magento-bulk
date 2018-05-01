<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnEditComments
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = '';

        $retour = '<textarea rows="3" cols="60" id="edit_comment[' . $row->getrsrp_id() . ']" name="edit_comment[' . $row->getrsrp_id() . ']">' . $row->getrsrp_comments() . '</textarea>';

        return $retour;
    }
}