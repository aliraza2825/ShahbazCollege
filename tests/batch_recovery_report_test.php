<?php
define('BASEPATH',__DIR__);
require __DIR__.'/../libraries/Batch_recovery_report.php';
function verify($ok,$label) { if (!$ok) throw new Exception($label); echo "PASS: $label\n"; }
function student($id,$course,$batch) { return array('student_id'=>$id,'course_id'=>$course,'course_name'=>'Course '.$course,'session'=>$batch,'roll_no'=>'R'.$id,'first_name'=>'Student','last_name'=>(string)$id,'mobile'=>'03000000000'); }
function payment($id,$sid,$paid,$amount,$actual,$deadline,$date='',$merged='',$fallback='') { return array('id'=>$id,'student_id'=>$sid,'paid'=>$paid,'amount'=>$amount,'actual_amount'=>$actual,'dead_line'=>$deadline,'actual_paid_date'=>$date,'paid_date'=>$fallback,'merged_challan'=>$merged); }
$students=array(student(1,1,'2023-2025'),student(2,1,'2024-2026'),student(3,2,'2023-2025'),student(4,2,''));
$students[0]['struckoff_letters']=3;
$payments=array(
 payment(1,1,0,100.10,0,'2026-09-30'), payment(2,1,0,200.20,0,'2026-10-31'), payment(3,1,0,999,0,'2026-11-01'),
 payment(4,1,1,100,90,'2025-01-01','2026-10-01'),
 payment(5,1,1,200,450,'2026-01-01','2026-10-14','MERGE'), payment(6,1,1,300,450,'2026-02-01','2026-10-14','MERGE'),
 payment(7,2,1,80,80,'2026-10-01','0000-00-00','','2026-10-31'),
 payment(8,2,1,60,60,'2026-10-01','2026-11-01','','2026-10-14'),
 payment(9,3,1,45,45,'2026-10-01','2026-10-14','MERGE'),
 payment(10,999,0,1000,0,'2026-01-01'), payment(11,1,0,1000,0,'0000-00-00'),
 payment(12,1,1,10,10,'2026-10-01','2026-09-30'),
);
$r=(new Batch_recovery_report)->build($students,$payments,'2026-10','2026-10-31');
verify(abs($r['courses'][0]['students'][0]['due']-300.30)<0.001,'arrears and cutoff included; future and invalid deadlines excluded');
verify($r['courses'][0]['students'][0]['recovery']==540,'received amount used and merged receipt counted once');
verify($r['courses'][0]['students'][1]['recovery']==80,'receipt date wins; fallback and month boundaries work');
verify($r['totals']['2023-2025']['recovery']==585,'same merge label on different students remains separate');
verify($r['student_count']==4 && count($r['courses'])==2 && count($r['batches'])==3,'courses, all batches and unassigned students retained');
verify($r['batches']===array('2024-2026','2023-2025','Unassigned'),'newest batch first; unassigned last');
verify($r['totals']['2024-2026']['recovery']==80 && $r['totals']['Unassigned']['due']==0,'grand totals include each course once and retain zeros');
$empty=(new Batch_recovery_report)->build(array(),$payments,'2026-10','2026-10-31');
verify(!$empty['courses'] && !$empty['totals'],'out of scope payments excluded');

verify($r['courses'][0]['students'][0]['struckoff_letters']===3 && $r['courses'][0]['students'][1]['struckoff_letters']===0,'letter counts retained with zero for no letters');
