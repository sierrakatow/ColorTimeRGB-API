<?php
include('./connection.php');


$UID = $_GET['UID'];
$CTime = $_GET['CTime'];
$NumC = $_GET['NumC'];
$SCEM = $_GET['SCEM'];
$Pat = $_GET['Pat'];
$R1 = $_GET['R1'];
$G1 = $_GET['G2'];
$B1 = $_GET['B2'];
$R2 = $_GET['R2'];
$G2 = $_GET['G2'];
$B2 = $_GET['B2'];



$select_str='INSERT INTO MasterRecords (UserID,CurTime,NumColors,Scheme,Pattern,R1,G1,B1,R2,G2,B2) VALUES ("'.$UID.'",'.$CTime.','.$NumC.','.$SCEM.','.$Pat.','.$R1.','.$G1.','.$B1.','.$R2.','.$G2.','.$B2.')';



$select = $pdo->prepare($select_str);
$select->execute();


?> 