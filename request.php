<?php
include('./connection.php');

if(isset($_GET['color1'])) {
    $color1 = explode(',', $_GET['color1']);

    for($i=0;$i<3;$i++){
        $color1[$i] = intval($color1[$i]);
    }
}else{
    exit();
}

if(isset($_GET['limit']) && is_int($_GET['limit'])){
    $limit = $_GET['limit'];
}

$select_str = 'SELECT * FROM items 
    WHERE `R1` = :R1 
    AND `G1` = :G1 
    AND `B1` = :B1';

if(isset($limit)) $select_str .= ' LIMIT :lim'; // Add limit to str

$select = $pdo->prepare($select_str);

$select->bindParam(':R1', $color1[0], PDO::PARAM_INT);
$select->bindParam(':G1', $color1[1], PDO::PARAM_INT);
$select->bindParam(':B1', $color1[2], PDO::PARAM_INT);

if(isset($limit)) $select = $pdo->prepare($select_str); // Add limit value

try{
    $select->execute();
    $result = $select->fetchAll(\PDO::FETCH_OBJ);
    foreach($result as $row){
        print_r($row);
    }
}catch(\PDOException $ex){
    print($ex->getMessage());
    echo "\n";
}

?> 