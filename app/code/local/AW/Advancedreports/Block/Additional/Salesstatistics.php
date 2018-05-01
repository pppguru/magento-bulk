<?php
    /**
     * aheadWorks Co.
     *
     * NOTICE OF LICENSE
     *
     * This source file is subject to the EULA
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://ecommerce.aheadworks.com/LICENSE-M1.txt
     *
     * @category   AW
     * @package    AW_ARUnits_Salesstatistics
     * @copyright  Copyright (c) 2009-2011 aheadWorks Co. (http://www.aheadworks.com)
     * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
     */
?>
<?php
class AW_Advancedreports_Block_Additional_Salesstatistics extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'additional_salesstatistics';
        $this->_headerText = Mage::helper('advancedreports')->__(Mage::helper('advancedreports/additional')->getReports()->getTitle('salesstatistics'));
        parent::__construct();
        $this->_removeButton('add');
    }
}
