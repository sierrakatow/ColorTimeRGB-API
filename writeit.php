<?php
include('./connection.php');


$select_str="INSERT INTO MasterRecords (UserID) VALUES (1031)";



$select = $pdo->prepare($select_str);
$select->execute();


?> 