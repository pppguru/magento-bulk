<?php

class MDN_Organizer_Block_Widget_Column_Renderer_Comments
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$entity = $this->getColumn()->getentity();
    	$entity_id = $row->getId();
    	if ($this->getColumn()->getentity_id_field() != '')
    		$entity_id = $row->getData($this->getColumn()->getentity_id_field());
    	$content = mage::helper('Organizer')->getEntityCommentsSummary($entity, $entity_id, true);
    	$html = '';
    	if ($content != '')
	    	$html = '<a href="#" class="lien-popup"><img src="'.$this->getSkinUrl('images/Organizer/comments.gif').'"><span>'.$content.'</span></a>';
    	return $html;
    }
    
}