<?php
include('./connection.php');


$UID = $_GET['UID'];
$CTime = $_GET['CTime'];
$NumC = $_GET['NumC'];
$SCEM = $_GET['SCEM'];
$Pat = $_GET['Pat'];
$R1 = $_GET['R1'];
$G1 = $_GET['G1'];
$B1 = $_GET['B1'];
$R2 = $_GET['R2'];
$G2 = $_GET['G2'];
$B2 = $_GET['B2'];
$IL = $_GET['Link'];
$CAT = $_GET['Cat'];



$select_str='INSERT INTO MasterClicks (UserID,CurTime,NumColors,Scheme,Pattern,R1,G1,B1,R2,G2,B2,ITEMLINK,CATEGORY) VALUES ("'.$UID.'",'.$CTime.','.$NumC.','.$SCEM.','.$Pat.','.$R1.','.$G1.','.$B1.','.$R2.','.$G2.','.$B2.','.$IL.','.$CAT.')';

echo $select_str;

$select = $pdo->prepare($select_str);
$select->execute();


?> 