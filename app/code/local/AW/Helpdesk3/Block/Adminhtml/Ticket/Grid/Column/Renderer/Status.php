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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{

    protected $_cachedData = null;

    public function render(Varien_Object $row)
    {
        $value = (int)$this->_getValue($row);
        $style = "";
        $bgColor = $this->_getBackgroundColorByStatusId($value);
        if (null !== $bgColor) {
            $style .= "background-color:{$bgColor};";
        }
        $textColor = $this->_getTextColorByStatusId($value);
        if (null !== $textColor) {
            $style .= "color:{$textColor};";
        }
        return "<span class='awhdu3-ticket-grid-status-column-cell' style='{$style}'>" . parent::render($row) . "</span>";
    }

    /**
     * @param int $statusId
     *
     * @return null|string
     */
    protected function _getBackgroundColorByStatusId($statusId)
    {
        $data = $this->_getStatusData($statusId);
        if (null === $data || !array_key_exists('background_color', $data) || empty($data['background_color'])) {
            return null;
        }
        $color = $data['background_color'];
        $color = (strpos($color, '#') === FALSE)?('#' . $color):$color;
        return $color;
    }

    /**
     * @param int $statusId
     *
     * @return null|string
     */
    protected function _getTextColorByStatusId($statusId)
    {
        $data = $this->_getStatusData($statusId);
        if (null === $data || !array_key_exists('font_color', $data) || empty($data['font_color'])) {
            return null;
        }
        $color = $data['font_color'];
        $color = (strpos($color, '#') === FALSE)?('#' . $color):$color;
        return $color;
    }

    /**
     * @param int $statusId
     *
     * @return null|array
     */
    protected function _getStatusData($statusId)
    {
        if (null === $this->_cachedData) {
            /** @var AW_Helpdesk3_Model_Resource_Ticket_Status_Collection $collection */
            $collection = Mage::getModel('aw_hdu3/ticket_status')->getCollection()->addNotDeletedFilter();
            foreach ($collection->getData() as $value) {
                $this->_cachedData[$value['id']] = $value;
            }
        }
        if (!array_key_exists($statusId, $this->_cachedData)) {
            return null;
        }
        return $this->_cachedData[$statusId];
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $result = parent::renderExport($row);
        return strip_tags($result);
    }
}