<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Geoip_Adminhtml_Customer_Online_Grid extends Mage_Adminhtml_Block_Customer_Online_Grid
{
    protected function _prepareColumns()
    {
    	parent::_prepareColumns();

		$this->addColumn('geoip', array(
            'header'    => Mage::helper('geoip')->__('IP Location'),
            'index'     => 'remote_addr',
			'align'     => 'centre',
            'width'     => 200,
            'renderer'  => 'mageworx/geoip_adminhtml_customer_online_grid_renderer_geoip',
            'filter'    => false,
            'sortable'  => false,
        ));

        foreach ($this->_columns as $_id => $_column) {
            if ($_id == 'geoip') continue;
            $_columns[$_id] = $_column;
            if ($_id == 'ip_address') {
                $_columns['geoip'] = $this->_columns['geoip'];
            }
        }
        $this->_columns = $_columns;

        return $this;
    }
}
