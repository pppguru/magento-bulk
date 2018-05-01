<?php

class MDN_ExtensionConflict_Block_Widget_Grid_Column_Renderer_IsConflict
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $color = 'green';
        $text = 'No';
        $link = '';
        if ($row->getec_is_conflict()){
            $color = 'red';
            $text = 'Yes';
            $link = '<br/>'.$this->getBackupLink($row->getId());
            $link .= '<br/>'.$this->getFixLink($row->getId());
        }
        return '<font color="'.$color.'">'.$this->__($text).'</font>'.$link;
    }

    public function getFixLink($id){
        $html='';
        if($id>0) {
            $url = $this->getUrl('adminhtml/ExtensionConflict_Admin/DisplayFix', array('ec_id' => $id));
            $html = '<a href="' . $url . '">' . $this->__('Display fix') . '</a>';
        }
        return $html;
    }

    public function getBackupLink($id){
        $html='';
        if($id>0) {
            $url = $this->getUrl('adminhtml/ExtensionConflict_Admin/BackupConflict', array('ec_id' => $id));
            $html = '<a href="' . $url . '">' . $this->__('Backup conflict') . '</a>';
        }
        return $html;
    }

}