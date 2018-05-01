<?php

class MDN_CompetitorPrice_Model_Observer
{

    /**
     * Add column in catalog > products grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function controller_action_layout_render_before_adminhtml_catalog_product_index(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('CompetitorPrice')->isEnabled())
            return;

        $gridBlock = Mage::getSingleton('core/layout')->getBlock('admin.product.grid');
        if (!$gridBlock)
        {
            if (Mage::getSingleton('core/layout')->getBlock('products_list'))
                $gridBlock = Mage::getSingleton('core/layout')->getBlock('products_list')->getChild('grid');
        }


        if ($gridBlock)
        {
            $gridBlock->addColumnAfter('competitor_price', array(
                'header'=> Mage::helper('CompetitorPrice')->__('Price Tracker'),
                'index' => 'entity_id',
                'renderer'	=> 'MDN_CompetitorPrice_Block_Grid_Column_Renderer_Ean',
                'filter' => 'MDN_CompetitorPrice_Block_Grid_Column_Filter_Filter',
                'sortable'	=> false,
            ), 'price');
        }

        return $this;
    }

    public function advancedstock_product_grid_preparecolumns(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('CompetitorPrice')->isEnabled())
            return;

        $gridBlock = $observer->getEvent()->getGrid();

        if ($gridBlock)
        {
            $gridBlock->addColumnAfter('competitor_price', array(
                'header'=> Mage::helper('CompetitorPrice')->__('Price Tracker'),
                'index' => 'entity_id',
                'renderer'	=> 'MDN_CompetitorPrice_Block_Grid_Column_Renderer_Ean',
                'filter' => 'MDN_CompetitorPrice_Block_Grid_Column_Filter_Filter',
                'sortable'	=> false,
            ), 'sell_price');
        }

        return $this;
    }

    public function marketplace_products_grid_addcolumns(Varien_Event_Observer $observer){

        if (!Mage::helper('CompetitorPrice')->isEnabled())
            return;

        $gridBlock = $observer->getEvent()->getGrid();

        if($gridBlock && in_array($gridBlock->getId(), array('up_to_date_products_grid', 'waiting_for_update_products_grid'))){

            $gridBlock->addColumnAfter('competitor_price', array(
                'header'=> Mage::helper('CompetitorPrice')->__('Price Tracker'),
                'index' => 'entity_id',
                'renderer'	=> 'MDN_CompetitorPrice_Block_Grid_Column_Renderer_Reference',
                'filter' => 'MDN_CompetitorPrice_Block_Grid_Column_Filter_Filter',
                'sortable'	=> false,
            ), 'sell_price');

        }

        return $this;

    }


}