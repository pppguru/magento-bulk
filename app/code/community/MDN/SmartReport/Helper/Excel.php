<?php

class MDN_SmartReport_Helper_Excel extends Mage_Core_Helper_Abstract {

    public function fromArray($collection)
    {
        $excel = '
                <?xml version="1.0"?>
                <?mso-application progid="Excel.Sheet"?>
                <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">
                    <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office"/>
                    <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel"/>
                    <Worksheet ss:Name="Sheet 1">
                        <Table>
                ';

        $isFirst = true;
        foreach($collection as $item)
        {
            if ($isFirst)
            {
                $excel .= '<Row>';
                foreach($item as $k => $v) {
                    $excel .= '<Cell><Data ss:Type="String">' . $k . '</Data></Cell>';
                }
                $excel .= '</Row>';
                $isFirst = false;
            }

            $excel .= '<Row>';

            foreach($item as $k => $v)
            {
                $excel .= '<Cell><Data ss:Type="String">'.$v.'</Data></Cell>';
            }

            $excel .= '</Row>';
        }

        $excel .= '
                        </Table>
                    </Worksheet>
                </Workbook>
                    ';


        return $excel;
    }

}
