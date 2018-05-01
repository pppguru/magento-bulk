<?php

/**
 * Class MyExtensions
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Block_System_Config_Form_FieldSet_MyExtensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return html
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getLayout()->createBlock('MyBms/MyExtensions')->toHtml();
    }
}