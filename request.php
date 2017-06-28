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

if(isset($_GET['limit'])){
    $limit = intval($_GET['limit']);
}

$select_str = 'SELECT items.id, items.link, items.img_url, items.price, items.category_id, items.serial_number, 
    CONCAT(items.R1, \', \', items.G1, \', \', items.B1, \', \', items.P1) as color1, 
    CONCAT(items.R2, \', \', items.G2, \', \', items.B2, \', \', items.P2) as color2, 
    CONCAT(items.R3, \', \', items.G3, \', \', items.B3, \', \', items.P3) as color3, 
    CONCAT(items.R4, \', \', items.G4, \', \', items.B4, \', \', items.P4) as color4
    FROM items
    WHERE `R1` = :R1 
    AND `G1` = :G1 
    AND `B1` = :B1';

if(isset($limit)) $select_str .= ' LIMIT :lim'; // Add limit to str

$select = $pdo->prepare($select_str);

$select->bindParam(':R1', $color1[0], PDO::PARAM_INT);
$select->bindParam(':G1', $color1[1], PDO::PARAM_INT);
$select->bindParam(':B1', $color1[2], PDO::PARAM_INT);

if(isset($limit)) $select->bindParam(':lim', $limit, PDO::PARAM_INT); // Add limit value

try{
    $select->execute();
    $result = $select->fetchAll(\PDO::FETCH_OBJ);
    foreach($result as $row){
        print(json_encode($row)."\n");
    }
}catch(\PDOException $ex){
    print($ex->getMessage());
    echo "\n";
}

?> 