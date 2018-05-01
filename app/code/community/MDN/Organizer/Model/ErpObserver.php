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
class MDN_Organizer_Model_ErpObserver {

    /**
     * Add custom tabs to erp product sheet
     *
     */
    public function advancedstock_product_edit_create_tabs(Varien_Event_Observer $observer) {

        //init vars
        $tab = $observer->getEvent()->gettab();
        $product = $observer->getEvent()->getproduct();
        $layout = $observer->getEvent()->getlayout();

        //add custom tab
        $TaskCount = 0;
        $gridBlock = $layout
                ->createBlock('Organizer/Task_Grid')
                ->setEntityType('product')
                ->setEntityId($product->getId())
                ->setShowTarget(false)
                ->setShowEntity(false)
                ->setTemplate('Organizer/Task/List.phtml');

        $content = $gridBlock->toHtml();

        $TaskCount = $gridBlock->getCollection()->getSize();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/organizer')) {
            $tab->addTab('tab_organizer', array(
                'label' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
                'title' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
                'content' => $content,
            ));
        }
    }

    /**
     * Add organizer tab in Product Return sheet
     *
     * @param Varien_Event_Observer $observer
     */
    public function productreturn_edit_create_tabs(Varien_Event_Observer $observer) {

        //init vars
        $tab = $observer->getEvent()->gettab();
        $rma = $observer->getEvent()->getrma();
        $layout = $observer->getEvent()->getlayout();


        //add custom tab
        $TaskCount = 0;
        $gridBlock = $layout
                ->createBlock('Organizer/Task_Grid')
                ->setEntityType('rma')
                ->setEntityId($rma->getId())
                ->setShowTarget(false)
                ->setShowEntity(false)
                ->setTemplate('Organizer/Task/List.phtml');

        $content = $gridBlock->toHtml();

        $TaskCount = $gridBlock->getCollection()->getSize();
        $tab->addTab('tab_organizer', array(
            'label' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'title' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'content' => $content,
        ));
    }

    /**
     * Add organizer column to rma grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function productreturn_grid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('organiser', array(
            'header' => Mage::helper('Organizer')->__('Organizer'),
            'renderer' => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'rma',
            'filter' => false,
            'sort' => false
        ));
    }

    /**
     * Add custom columns to sales order grid
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function advancedstock_sales_order_grid_before_render(Varien_Event_Observer $observer) {

        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('increment_id', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
            'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'order',
            'filter' => false,
            'sortable' => false,
            'width' => '60px'
        ),'real_order_id');


        return $this;
    }

    public function customer_grid_before_render(Varien_Event_Observer $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('organizer', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
            'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'customer',
            'filter' => false,
            'sortable' => false,
            'width' => '60px'
        ),'real_order_id');

        return $this;
    }

}

