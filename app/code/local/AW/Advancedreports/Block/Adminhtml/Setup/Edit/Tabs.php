<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('advancedreports_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('advancedreports')->__('Report Customization'));
    }

    /**
     * Retrieves setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    protected function  _beforeToHtml()
    {

        $tabTitle = Mage::helper('advancedreports')->__('General');
        $tabBlock = $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_general');
        $this->addTab(
            'general',
            array(
                'label'   => $tabTitle,
                'title'   => $tabTitle,
                'content' => $tabBlock->toHtml(),
            )
        );

        if ($this->getSetup()->getGrid()->getCustomColumnConfigEnabled()) {
            $tabTitle = Mage::helper('advancedreports')->__('Columns');
            $tabBlock = $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_columns');
            $this->addTab(
                'columns',
                array(
                    'label'   => $tabTitle,
                    'title'   => $tabTitle,
                    'content' => $tabBlock->toHtml()
                )
            );
        }
        parent::_beforeToHtml();
    }
}
