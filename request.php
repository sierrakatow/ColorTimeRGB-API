<?php
include('./connection.php');

$max_cluster_count = 4;
$threshold = 80;

// CHECK PARAMETERS
if($_GET['color1'] !== null) {
    $color1 = explode(',', $_GET['color1']);
    // get integer from color cluster GET var
    foreach($color1 as $j => $c){
        $color1[$j] = intval($c);
    }

    if($_GET['colors2'] !== null){
        $color2 = explode(',', $_GET['color2']);
        // get integer from color cluster GET var
        foreach($color2 as $j => $c){
            $color2[$j] = intval($c);
        }
    }
    // percentage filters only present if color1 exists
    $pmin = ($_GET['pmin'] === null) ? null : intval($_GET['pmin']);
    $pmax = ($_GET['pmax'] === null) ? null : intval($_GET['pmax']);
    
}elseif($_GET['colorscheme'] !== null){
    $colorscheme = intval($_GET['colorscheme']);
}

$category = ($_GET['category'] === null) ? null : intval($_GET['category']);
$pattern = ($_GET['pattern'] === null) ? null : intval($_GET['pattern']);

$limit = ($_GET['limit'] === null) ? null : intval($_GET['limit']); // DEFINE LIMIT
$offset = ($_GET['offset'] === null) ? null : intval($_GET['offset']); // DEFINE OFFSET


if($colors1 !== null){
    $select_str = 'SELECT ic.item_id, items.link, items.img_url, items.price, items.category_id, items.serial_number,
        items.R1, items.G1, items.B1, items.P1,
        items.R2, items.G2, items.B2, items.P2,
        items.R3, items.G3, items.B3, items.P3,
        items.R4, items.G4, items.B4, items.P4';
}else{
    $select_str = 'SELECT id as `item_id`, link, img_url, price, category_id, serial_number, 
        R1, G1, B1, P1, 
        R2, G2, B2, P2, 
        R3, G3, B3, P3, 
        R4, G4, B4, P4
    FROM items';
}

// SINGLE COLOR QUERY
if($color1 !== null){
    if($color2 === null) {
        // Single Color
        $select_str .= ', SQRT(POW(ic.R-:R1, 2) + 
                                    POW(ic.G-:G1, 2) + 
                                    POW(ic.B-:B1, 2))
                        AS `distance`';
    }else{
        // Double Color
        $select_str .= ', SQRT(';
    }
}

// FROM TABLE
if($color1 !== null) {
    if($color2 === null) {
        $select_str .= ' FROM items_colors ic';
    } else {
        $select_str .= ' FROM items_colors2 ic';
    }
}

// JOIN ON ITEMS DETAILS
if($color1 !== null) $select_str .= ' INNER JOIN items ON items.id = ic.item_id';

// ADD WHERE
if($category !== null || $pmin !== null || $pmax !== null || $colorscheme !== null || $pattern !== null){
    $select_str .= ' WHERE';
}



// FILTER
if($category !== null) {
    if($color1 === null) $select_str .= ' category_id = :category';
    else $select_str .= ' items.category_id = :category';
}

if($pmin !== null){
    $select_str .= ' ic.P >= :pmin';
}

if($pmax !== null){
    $select_str .= ' ic.P <= :pmax';
}

// ORDER WITH COLOR CLUSTERS
if($color1 !== null){
    if($color2 === null) { 
    // Single Color
        $select_str .= ' HAVING distance < \''.$threshold.'\' ORDER BY distance, ic.P DESC, ic.item_id'; 
    }else{
    // Double Color

    }
}else{
    $select_str .= ' ORDER BY item_id';
}



if($limit !== null) $select_str .= ' LIMIT :lim'; // ADD LIMIT TO QRY
if($offset !== null) $select_str .= ' OFFSET :offset'; // ADD OFFSET TO QRY

// print $select_str;

$select = $pdo->prepare($select_str);

// BIND COLOR PARAMS
if($color1 !== null){
    // Single Color
    $select->bindParam(':R1', $color1[0], PDO::PARAM_INT);
    $select->bindParam(':G1'.$i, $color1[1], PDO::PARAM_INT);
    $select->bindParam(':B1'.$i, $color1[2], PDO::PARAM_INT);

    // Double Color
    if($color2 !== null){
        $select->bindParam(':R2', $color2[0], PDO::PARAM_INT);
        $select->bindParam(':G2'.$i, $color2[1], PDO::PARAM_INT);
        $select->bindParam(':B2'.$i, $color2[2], PDO::PARAM_INT);
    }

    // Percentage
    if($pmin !== null) $select->bindParam(':pmin', $pmin);
    if($pmax !== null) $select->bindParam(':pmax', $pmax);
}

if($category !== null) $select->bindParam(':category', $category, PDO::PARAM_INT); // BIND CATEGORY ID
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
    $result = $select->fetchAll(PDO::FETCH_ASSOC);

    // format color clusters
    foreach($result as $k => $row){
        $result[$k]['colors'] = array();
        for($i=1;$i<=$max_cluster_count;$i++){
            array_push($result[$k]['colors'], array($row['R'.$i], $row['G'.$i], $row['B'.$i], $row['P'.$i]));
            unset($result[$k]['R'.$i]);
            unset($result[$k]['G'.$i]);
            unset($result[$k]['B'.$i]);
            unset($result[$k]['P'.$i]);
        }
    }

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