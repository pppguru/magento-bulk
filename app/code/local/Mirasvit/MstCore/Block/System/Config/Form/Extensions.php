<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.2
 * @build     428
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Block_System_Config_Form_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= '<table class="form-list">';
        $html .= '<tr><th style="padding: 5px;">Extension</th><th style="padding: 5px;">Your Version</th><th style="padding: 5px;">Latest Version</th><th></th></tr>';
        foreach ($this->getExtensions() as $extension) {
            $html .= $this->_renderExtension($extension);
        }
        $html .= '</table>';

        $url = Mage::getSingleton('adminhtml/url')->getUrl('mstcore/adminhtml_validator/index', array('modules' => ''));

        $html .= '<br><button onclick="window.location=\''.$url.'\'" type="button"><span>Run validation tests for all extensions</span></button>';

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _renderExtension($ext)
    {
        $tds = array();
        $tds[] = '<a href="'.$ext->getUrl().'">'.$ext->getName().'</a>';
        $tds[] = $ext->getVersion();
        $tds[] = $ext->getLatest();


        $modules = array();
        $path = $ext->getPath();
        $path = explode('|', $path);
        foreach ($path as $p) {
            $p = explode('/', $p);
            $modules[] = $p[0];
        }
        $modules = implode(',', $modules);
        $url = Mage::getSingleton('adminhtml/url')->getUrl('mstcore/adminhtml_validator/index', array('modules' => $modules));

        $tds[] = '<button onclick="window.location=\''.$url.'\'" type="button"><span>Run validation tests</span></button>';
        $tds[] = '';

        $html = '<tr>';
        foreach ($tds as $value) {
            $html .= '<td style="padding: 5px;">'.$value.'</td>';
        }
        $html .= '</tr>';

        return $html;
    }

    protected function getExtensions()
    {
        $result     = array();
        $extensions = Mage::helper('mstcore/code')->getOurExtensions();
        $list       = Mage::getModel('mstcore/feed_extensions')->getList();

        foreach ($extensions as $extension) {
            if (!isset($list[$extension['s']])) {
                continue;
            }
            $info = $list[$extension['s']];

            $version = $extension['v'].'.'.$extension['r'];
            if ($version == '.') {
                $version = '-';
            }

            $result[$extension['s']] = new Varien_Object(array(
                'extension' => $extension['s'],
                'version'   => $version,
                'name'      => $info['name'],
                'url'       => $info['url'],
                'latest'    => $info['version'].'.'.$info['revision'],
                'path'      => $extension['p'],
            ));
        }
        return $result;
    }
}
