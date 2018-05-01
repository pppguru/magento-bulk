<?php

class MDN_MyBms_Block_UpdateNotifications extends Mage_Adminhtml_Block_Widget_Form {

    protected function _toHtml() {
        $html = '';

        if (Mage::getStoreConfig('MyBms/notifications/enable_new_versions'))

        $extensions = Mage::helper('MyBms/MyExtensions')->listMyExtensions();
        $outDatedExtensions = array();
        foreach($extensions as $extension)
        {
            if (isset($extension['up_to_date']))
            {
                if ((!$extension['up_to_date']) && (strlen($extension['name']) > 3))
                    $outDatedExtensions[] = $extension['name'];
            }
        }

        if (count($outDatedExtensions) > 0) {
            $message = '<b>' . 'Some Boostmyshop Extensions are not up to date : ' . '</b> ' . implode(', ', $outDatedExtensions);
            $message .= ' - <a href="'.$this->getUrl('adminhtml/system_config/edit', array('section' => 'MyBms')).'">'.$this->__('More details').'</a>';
            $html = '<div class="notification-global">' . $message . '</div>';
        }

        return $html;
    }

}