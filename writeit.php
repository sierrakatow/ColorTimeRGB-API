<?php
include('./connection.php');


$UID = $_GET['UID'];



$select_str='INSERT INTO MasterRecords (UserID) VALUES ('.$UID.')';



$select = $pdo->prepare($select_str);
$select->execute();


?> 