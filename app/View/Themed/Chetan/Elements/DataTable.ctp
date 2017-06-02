<?php

$isdatatable = isset($this->DataTable) ? $this->DataTable : TRUE;
$dclass = isset($this->DataTableClass) ? $this->DataTableClass : '';
$did = isset($this->DataTableId) ? $this->DataTableId : 'data_table';
$dopt = isset($this->DataTableOption) ? $this->DataTableOption : array();
$HEADER = isset($this->DataTableHeader) ? $this->DataTableHeader : array();
$FOOTER = isset($this->DataTableFooter) ? $this->DataTableFooter : array();
unset($this->DataTableHeader);
$DATA = isset($this->DataTableData) ? $this->DataTableData : array();
unset($this->DataTableData);
if ($isdatatable) {
    echo $this->Html->css(array('datatables.min'));
    echo $this->Html->tag('style', '.dtmh div div.row{margin-right:0px;margin-left:0px;}');
    echo $this->Html->script(array('datatables.min'));
}
echo $this->Html->div('dtmh', null, array('style' => 'overflow-y:auto;'));
echo $this->Html->tag('table', null, array('id' => $did, 'class' => "table table-bordered table-hover $dclass", 'cellspacing' => "0", 'style' => 'width:100%;border:1px solid #76cff2;'));
if (count($HEADER) > 0) {
    $thead = '';
    $tfoot = '';
    foreach ($HEADER as $ky => $value) {
        $key = explode("~!~", $value);
        $thead .= '<th style="border: 1px solid #76cff2;text-align:' . (isset($key[1]) ? $key[1] : 'left') . ';">' . $key[0] . '</th>';
        if (count($FOOTER)) {
            $tfoot .= '<th style="border: 1px solid #76cff2;text-align:' . (isset($key[1]) ? $key[1] : 'left') . ';">' . $FOOTER[$ky] . '</th>';
        }
    }
    echo $this->Html->tag('thead', $this->Html->tag('tr', $thead), array('style' => 'background: rgb(118, 207, 242);white-space:nowrap;'));
    if (trim($tfoot) != '') {
        echo $this->Html->tag('tfoot', $this->Html->tag('tr', $tfoot), array('style' => 'background: rgb(118, 207, 242);'));
    }
    echo $this->Html->tag('tbody');
    foreach ($DATA as $row) {
        $style = isset($row['css']) ? $row['css'] : '';
        echo '<tr style="' . $style . '">';
        foreach ($HEADER as $key => $val) {
            $k = explode("~!~", $val);
            echo '<td style="border: 1px solid #76cff2;text-align:' . (isset($k[1]) ? $k[1] : 'left') . ';">' . $row[$key] . '</td>';
        }
        echo '</tr>';
    }
    echo $this->Html->useTag('tagend', 'tbody');
}
echo $this->Html->useTag('tagend', 'table');
echo $this->Html->useTag('tagend', 'div');
if ($isdatatable) {
    echo $this->Html->tag('script', '$(document).ready(function () {$(\'#' . $did . '\').DataTable(' . json_encode($dopt) . ');});');
}