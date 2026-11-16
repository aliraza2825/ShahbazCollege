<?php
// Run with legacy PHP (PHPExcel's supported runtime).
define('BASEPATH',__DIR__);
date_default_timezone_set('Asia/Karachi');
require __DIR__.'/../libraries/PHPExcel.php';
require __DIR__.'/../libraries/Paypro_settlement_import.php';
$book = new PHPExcel;
$sheet = $book->createSheet();
foreach (array('College','Student','000123','ORD1','16-11-2026','15-11-2026','1,250.50','','','1Link','1') as $col=>$value) {
    $sheet->setCellValueExplicitByColumnAndRow($col,2,$value,PHPExcel_Cell_DataType::TYPE_STRING);
}
$importer = new Paypro_settlement_import;
foreach (array('Excel2007','Excel5') as $format) {
    $path = tempnam(sys_get_temp_dir(),'paypro_reader_');
    PHPExcel_IOFactory::createWriter($book,$format)->save($path);
    $loaded = PHPExcel_IOFactory::load($path);
    $parsed = $importer->parse($loaded);
    if ($parsed['rows'][0]['paid_amount'] !== 1250.5 || $parsed['rows'][0]['paypro_id'] !== '000123') throw new Exception('Wrong file values');
    $loaded->disconnectWorksheets();
    echo "PASS: $format file import preserves amounts and IDs\n";
    if ($format === 'Excel2007') {
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('xl/theme/theme1.xml','<?xml version="1.0"?><a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="PayPro"><a:themeElements/></a:theme>');
        $zip->close();
        $loaded = PHPExcel_IOFactory::load($path);
        $parsed = $importer->parse($loaded);
        if ($parsed['rows'][0]['paid_amount'] !== 1250.5) throw new Exception('Partial theme lost cell values');
        $loaded->disconnectWorksheets();
        echo "PASS: incomplete theme imports without attributes() fatal\n";
    }
    unlink($path);
}

// PayPro exports prefix all spreadsheet XML elements with x: and use absolute
// relationship targets. Older PHPExcel silently returns an empty workbook.
$path = tempnam(sys_get_temp_dir(),'paypro_namespace_');
PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
$zip = new ZipArchive; $zip->open($path);
foreach (array('xl/workbook.xml','xl/sharedStrings.xml','xl/worksheets/sheet1.xml','xl/worksheets/sheet2.xml') as $part) {
    $xml = $zip->getFromName($part);
    if ($xml === false) continue;
    $xml = preg_replace('/(<\/?)([a-zA-Z][a-zA-Z0-9]*)(?=[\s>\/])/','$1x:$2',$xml);
    $xml = str_replace('xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"','xmlns:x="http://schemas.openxmlformats.org/spreadsheetml/2006/main"',$xml);
    $zip->addFromString($part,$xml);
}
$rels=$zip->getFromName('xl/_rels/workbook.xml.rels');
$rels=str_replace('Target="worksheets/','Target="/xl/worksheets/',$rels);
$zip->addFromString('xl/_rels/workbook.xml.rels',$rels); $zip->close();
$importer = new Paypro_settlement_import;
$r=$importer->read_xlsx($path);
if(count($r['rows'])!==1 || $r['rows'][0]['paypro_id']!=='000123' || $r['rows'][0]['paid_amount']!==1250.5) throw new Exception('Namespaced export mismatch');
echo "PASS: PayPro prefixed XML, shared strings and absolute worksheet targets\n";
unlink($path);
$sheet->setCellValueExplicitByColumnAndRow(11,1,'Paid Via',PHPExcel_Cell_DataType::TYPE_STRING);
$sheet->setCellValueExplicitByColumnAndRow(12,1,'Delay Time (In-Days)',PHPExcel_Cell_DataType::TYPE_STRING);
$sheet->setCellValueExplicitByColumnAndRow(9,2,'0',PHPExcel_Cell_DataType::TYPE_STRING);
$sheet->setCellValueExplicitByColumnAndRow(11,2,'MBL',PHPExcel_Cell_DataType::TYPE_STRING);
$sheet->setCellValueExplicitByColumnAndRow(12,2,'4',PHPExcel_Cell_DataType::TYPE_STRING);
$path = tempnam(sys_get_temp_dir(),'paypro_columns_');
PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
$r=$importer->read_xlsx($path);
if ($r['rows'][0]['paid_via'] !== 'MBL' || $r['rows'][0]['paid_after_days'] !== '4') throw new Exception('Shifted PayPro columns were misread');
echo "PASS: expanded PayPro export reads channel and delay by heading\n";
unlink($path);
$book->disconnectWorksheets();
