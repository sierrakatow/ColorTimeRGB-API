<?php
include('./connection.php');

$max_cluster_count = 4;

// COLOR CLUSTERS
$colors = array();

for($i=1;$i<=$max_cluster_count;$i++){
    if($_GET['color'.$i] !== null) {
        $colors[$i] = explode(',', $_GET['color'.$i]);
        // get integer from color cluster GET var
        foreach($colors[$i] as $j => $c){
            $colors[$i][$j] = intval($c);
        }
    }
}

$cluster_count = sizeof($colors);

$limit = ($_GET['limit'] === null) ? null : intval($_GET['limit']); // DEFINE LIMIT
$offset = ($_GET['offset'] === null) ? null : intval($_GET['offset']); // DEFINE OFFSET

$select_str = 'SELECT items_colors.item_id, items.link, items.img_url, items.price, items.category_id, items.serial_number,
    CONCAT(items.R1, \', \', items.G1, \', \', items.B1, \', \', items.P1) as color1, 
    CONCAT(items.R2, \', \', items.G2, \', \', items.B2, \', \', items.P2) as color2, 
    CONCAT(items.R3, \', \', items.G3, \', \', items.B3, \', \', items.P3) as color3, 
    CONCAT(items.R4, \', \', items.G4, \', \', items.B4, \', \', items.P4) as color4';

// ADD COLOR CLUSTERS TO QRY
for($i=1;$i<=$max_cluster_count;$i++){
    if(array_key_exists($i, $colors)){
        $select_str .= ', MIN(SQRT(POW(items_colors.R-:R'.$i.', 2) + POW(items_colors.G-:G'.$i.', 2) + POW(items_colors.B-:B'.$i.', 2))) AS `distance'.$i.'`';
    }
}

$select_str .= ' FROM items_colors INNER JOIN items ON items.id = items_colors.item_id';

// ORDER WITH COLOR CLUSTERS
if($cluster_count == 1){
    $select_str .= ' GROUP BY items_colors.item_id ORDER BY distance1, items_colors.item_id'; 
}elseif($cluster_count > 1){
    // $select_str .= ' ORDER BY SQRT(';
    // for($i=1;$i<$cluster_count;$i++){
    //     $select_str .= 'POW(distance'.$i.', 2)';
    //     if($i < $cluster_count-1) $select_str .= ' + ';
    // }
    // $select_str .= '), items_colors.item_id';
}



if($limit !== null) $select_str .= ' LIMIT :lim'; // ADD LIMIT TO QRY
if($offset !== null) $select_str .= ' OFFSET :offset'; // ADD OFFSET TO QRY


$select = $pdo->prepare($select_str);

// BIND COLOR PARAMS
for($i=1;$i<=$max_cluster_count;$i++){
    if(array_key_exists($i, $colors)){
        $select->bindParam(':R'.$i, $colors[$i][0], PDO::PARAM_INT);
        $select->bindParam(':G'.$i, $colors[$i][1], PDO::PARAM_INT);
        $select->bindParam(':B'.$i, $colors[$i][2], PDO::PARAM_INT);
    }
}

if($limit !== null) $select->bindParam(':lim', $limit, PDO::PARAM_INT); // BIND LIMIT VAL
if($offset !== null) $select->bindParam(':offset', $offset, PDO::PARAM_INT); // BIND OFFSET VAL

// SET NEXT & LAST LINKS
if($limit !== null) {
    if($offset === null) $next = preg_replace('/limit=[0-9]+/', 'limit='.$limit.'&offset='.$limit, $_SERVER[REQUEST_URI]);
    else {
        $lim_offset_diff = $offset - $limit;
        if($lim_offset_diff == 0) $last = preg_replace('/&offset=[0-9]+/', '', $_SERVER[REQUEST_URI]);
        if ($lim_offset_diff > 0) $last = preg_replace('/offset=[0-9]+/', 'offset='.($offset-$limit), $_SERVER[REQUEST_URI]);
        $next = preg_replace('/offset=[0-9]+/', 'offset='.($offset+$limit), $_SERVER[REQUEST_URI]);
    }
}

// EXECUTION + DATA RETRIEVAL
try{
    $select->execute();
    $result = $select->fetchAll(\PDO::FETCH_OBJ);

    $offset = ($offset === null) ? 0 : $offset;

    $output = array('meta' => array(
        'count' => sizeof($result),
        'limit' => $limit,
        'offset' => $offset,
        'self' => 'http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]
    ),
    'data' => $result);

    if($next !== null) $output['meta']['next'] = 'http://'.$_SERVER[HTTP_HOST].$next;
    if($last !== null) $output['meta']['last'] = 'http://'.$_SERVER[HTTP_HOST].$last;

    // FORMAT OUTPUT
    header('Content-Type: application/json');
    print(json_encode($output));
}catch(\PDOException $ex){
    print($ex->getMessage());
    echo "\n";
}

?> 