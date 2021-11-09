<?php
require_once("xlsxwriter.class.php");
require_once("SimpleXLSX.php");

$rows=SimpleXLSX::parse('statistics.xlsx')->rows(1);

$array = [];

foreach($rows as $val){
  $array[$val[0]] = ['qty' =>$val[1], 'mxn' => $val[2]];
}


header("content-type:application/json");
echo(json_encode($array));