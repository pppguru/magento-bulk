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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Block_Adminhtml_Filter_Renderer_Progress
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $ruleId = $row->getData('filter_id');
        $ruleProgress = $row->getData('progress_percent');

        if ($ruleProgress == 100) {
            $statusClass = 'grid-severity-notice';
            $statusLabel = $this->__('Ready');
        } elseif (is_null($ruleProgress)) {
            $statusClass = 'grid-severity-major';
            $statusLabel = $this->__('Reindex Required');
        } else {
            $statusClass = '';
            $statusLabel = $ruleProgress . '%';
        }
        return '<span id="progress_' . $ruleId . '" class="' . $statusClass . '">'
        . '<span class="aw-mss-status" style="display:none;">' . $statusLabel . '</span>'
        . '<span class="aw-mss-progress" style="display:none;">' . $ruleProgress . '</span>'
        . '</span>';
    }
}