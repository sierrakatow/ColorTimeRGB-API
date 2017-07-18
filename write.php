<?php
include('./connect_record.php');




$select_str="INSERT INTO MasterRecords (UserID) VALUES (101)";

$

$select = $pdo->prepare($select_str);
$select->execute();




?> 