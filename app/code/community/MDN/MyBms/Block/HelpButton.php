<?php

/**
 * Class HelpButton
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Block_HelpButton extends Mage_Adminhtml_Block_Abstract
{
    /**
     * @var null
     */
    protected $menu = null;

    /**
     * Helper button on the nav-bar
     */
    protected function _prepareLayout()
    {

        if(Mage::getStoreConfig('MyBms/documentation/enable_menu_display')) {
            $list = Mage::helper("MyBms/Data")->listDocumentation();
            $getController = Mage::app()->getRequest()->getControllerName();
            $getAction = Mage::app()->getRequest()->getActionName();
            $getParam = Mage::app()->getRequest()->getParam('section');
            $mca = $getController . '_' . $getAction;

            if ($list != null) {
                foreach ($list as $key => $value) {
                    if ($value['bmd_controller'] == $mca || $value['bmd_controller'] == $mca . $getParam) {
                        $explodeAll = explode("\n", $value['bmd_lien']);

                        foreach ($explodeAll as $explode) {
                            $explodeOne = explode(',', $explode);

                            if (count($explodeOne) == 2) {
                                $this->menu .= '<li class="li-doc"><a target="_blank" href=' . $explodeOne[1] . '><span style="color:#666E73">'
                                    . $explodeOne[0] . '</span></a></li>';
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return null | <li>
     */
    public function getMenu()
    {
        return $this->menu;
    }
}
