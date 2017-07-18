<?php
include('./connect_record.php');



$select_str="INSERT INTO MasterRecords (USERID,CurTime,NumColors,Scheme,Pattern,R1,G1,B1,R2,G2,B2) VALUES (16,1000,2,0,0,100,200,100,300,300,300)"
$select = $pdo->prepare($select_str);
$select->execute();




?> 