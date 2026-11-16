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
$book->disconnectWorksheets();
