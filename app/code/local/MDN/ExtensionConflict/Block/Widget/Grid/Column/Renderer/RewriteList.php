<?php

class MDN_ExtensionConflict_Block_Widget_Grid_Column_Renderer_RewriteList
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = $this->displayRewrites($row);
        $html .= $this->displayObjectTraceLink($row);

        return $html;
    }

    public function displayObjectTraceLink($row){
        $target = 'trace_result_'.$row->getec_core_class().'_'.$row->getec_core_module();
        $html = $this->getObjectTraceLink($target);
        $html .= $this->getJs($this->getObjectTraceUrl($row),$target);
        $html .= '<div id="'.$target.'"></div>';
        return $html;

    }

    public function displayRewrites($row){
        $list = explode(',', $row->getec_rewrite_classes());

        $lineBreak = '';
        if (count($list)>1) {
            $lineBreak = '<br>';
        }

        $html = '';

        foreach ($list as $class) {
            $html .= '<i>' . $class . '</i>'.$lineBreak;
        }

        return $html;
    }

    public function getJs($url,$target){

        $js = '<script type="text/javascript">
            function getObjectTraceAjax_'.$target.'() {
            var url = "'.$url.'";
                var request = new Ajax.Request(
                        url,
                    {
                        method: \'get\',
                        onSuccess: function onSuccess(transport) {
                    var response = transport.responseText;
                    document.getElementById("'.$target.'").innerHTML = response;
                },
                        onFailure: function onFailure(transport) {
                    document.getElementById("'.$target.'").innerHTML = \'ERROR HTTP\';
                }
                    }
                );
            }
        </script>';
        return $js;
    }

    public function getObjectTraceLink($target){

        return '<br><a href="javascript:getObjectTraceAjax_'.$target.'()">' . $this->__('Display Object Trace') . '</a>';
    }

    public function getObjectTraceUrl($row)
    {
        $coreClass = $row->getec_core_class();
        $coreModule = $row->getec_core_module();

        return $this->getUrl('adminhtml/ExtensionConflict_Admin/ObjectTraceAjax',
            array('core_module' => $coreModule,
                  'core_class' =>$coreClass)
            );
    }




}