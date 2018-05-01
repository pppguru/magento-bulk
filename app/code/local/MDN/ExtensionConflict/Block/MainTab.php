<?php

class MDN_ExtensionConflict_Block_Maintab extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('extensionconflicttabs');
        $this->setDestElementId('extensionconflicttabs');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
    }

    protected function _beforeToHtml() {

        $extName = 'ExtensionConflict';
        $controller = 'Admin';
        $urlPath = 'adminhtml/'.$extName.'_'.$controller;
        $helper = Mage::helper($extName);

        $title = 'Conflicts';
        $this->addTab($title, array(
            'label' => $helper->__($title.' List'),
            'content' => '<br/><br/>'.$this->getLayout()->createBlock('ExtensionConflict/List')
                    ->setTemplate($extName.'/ConflictList.phtml')->toHtml(),
            'active' => true
        ));

        $title = 'Extension';
        $this->addTab($title, array(
            'label' => $helper->__($title.' List'),
              'url'       => $this->getUrl($urlPath.'/'.$title.'ListAjax', array('_current' => true)),
              'class'     => 'ajax'
        ));

        $title = 'BackupedConflicts';
        $this->addTab($title, array(
            'label' => $helper->__('Backups'),
            'url'       => $this->getUrl($urlPath.'/'.$title.'Ajax', array('_current' => true)),
            'class'     => 'ajax'
        ));

        $title = 'Comparer';
        $this->addTab($title, array(
            'label' => $helper->__($title),
            'url'       => $this->getUrl($urlPath.'/'.$title.'Ajax', array('_current' => true)),
            'class'     => 'ajax'
        ));

        $title = 'VirtualModule';
        $this->addTab($title, array(
            'label' => $helper->__($title),
            'url'       => $this->getUrl($urlPath.'/'.$title.'Ajax', array('_current' => true)),
            'class'     => 'ajax'
        ));

        $title = 'Events';
        $this->addTab($title, array(
            'label' => $helper->__($title),
            'url'       => $this->getUrl($urlPath.'/'.$title.'Ajax', array('_current' => true)),
            'class'     => 'ajax'
        ));



        return parent::_beforeToHtml();
    }
}
