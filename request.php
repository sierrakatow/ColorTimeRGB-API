<?php
include('./connection.php');

$max_cluster_count = 4;
$threshold = 80;

// CHECK PARAMETERS
if($_GET['color1'] === null && $_GET['colorscheme'] === null && $_GET['pattern'] === null && $_GET['limit'] === null){
    header("HTTP/1.1 422 OK");
    header('Content-Type: application/json');
    $output = array(
        'error'=> 'Parameter requirements not met.'
    );
    print(json_encode($output));
    exit();
}

if($_GET['color1'] !== null) {
    $color1 = explode(',', $_GET['color1']);
    // get integer from color cluster GET var
    foreach($color1 as $j => $c){
        $color1[$j] = intval($c);
    }

    if($_GET['color2'] !== null){
        $color2 = explode(',', $_GET['color2']);
        // get integer from color cluster GET var
        foreach($color2 as $j => $c){
            $color2[$j] = intval($c);
        }

        $threshold = 150; // loosen threshold for 2 colors
    }
    // percentage filters only present if color1 exists
    $pmin = ($_GET['pmin'] === null) ? null : intval($_GET['pmin']);
    $pmax = ($_GET['pmax'] === null) ? null : intval($_GET['pmax']);
    
}elseif($_GET['colorscheme'] !== null){
    $colorscheme = strtolower($_GET['colorscheme']);
}
$category = ($_GET['category'] === null) ? null : intval($_GET['category']);
$pattern = ($_GET['pattern'] === null) ? null : strtolower($_GET['pattern']);

if($pattern == 'dotted' || $pattern == 'stripes') $threshold = 150; // loosen threshold

$limit = ($_GET['limit'] === null) ? 1000 : intval($_GET['limit']); // DEFINE LIMIT
$offset = ($_GET['offset'] === null) ? null : intval($_GET['offset']); // DEFINE OFFSET


if($color1 !== null){
    $select_str = 'SELECT items.*';
    // if($color2 === null) $select_str .= ', ic.rank AS `closest_to`';
    // else $select_str .= ', ic.rank1 AS `closest_to[1]`, ic.rank2 AS `closest_to[2]`';
}else{
    $select_str = 'SELECT *
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
        $select_str .= ', SQRT(POW(SQRT(POW(ic.R1-:R1, 2) + 
                                POW(ic.G1-:G1, 2) + 
                                POW(ic.B1-:B1, 2)), 2) +
                            POW(SQRT(POW(ic.R2-:R2, 2) + 
                                POW(ic.G2-:G2, 2) + 
                                POW(ic.B2-:B2, 2)), 2)) AS `distance`';
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

$double_color_percent = ($color2 === null) ? '' : '1';

if($pmin !== null){
    $select_str .= ' ic.P'.$double_color_percent.' >= :pmin';
}

if($pmax !== null){
    $select_str .= ' ic.P'.$double_color_percent.' <= :pmax';
}

if($colorscheme !== null){
    $select_str .= ' '.$colorscheme.' >= \'1\'';
}

if($pattern !== null){
    if($color1 === null) $select_str .= ' '.$pattern.' >= \'1\'';
    else $select_str .= ' items.'.$pattern.' >= \'1\'';
}

// HAVING THRESHOLD (COLOR DISTANCE)
if($color1 !== null) {
    $select_str .= ' HAVING distance < :threshold ';
}

// ORDER WITH COLOR CLUSTERS
if($color1 !== null){
    $select_str .= ' ORDER BY distance, ic.P'.$double_color_percent.' DESC'; 
    if($pattern !== null) $select_str .= ', items.'.$pattern.' DESC';
    $select_str .= ', items.id'; 
}else{
    $select_str .= ' ORDER BY ';
    if($colorscheme !== null) $select_str .= $colorscheme.' DESC,';
    if($pattern !== null) $select_str .= $pattern.' DESC,';
    $select_str .= 'id';
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

    // Threshold
    $select->bindParam(':threshold', $threshold, PDO::PARAM_INT);
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
    header("HTTP/1.1 200 OK");
    header('Content-Type: application/json');
    print(json_encode($output));

}catch(\PDOException $ex){
    // ERROR
    print($ex->getMessage());
    echo "\n";
}

?> 