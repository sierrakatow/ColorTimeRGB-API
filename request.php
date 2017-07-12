<?php
include('./connection.php');

// CONSTANTS
$max_cluster_count = 4;

// DEFAULTS
$default_limit = 100;
$default_major = 1;

// Distance Coefficients
$r_coeff = 2;
$g_coeff = 4;
$b_coeff = 3;

// Color Thresholds - Starting Values
$single_color_threshold = 6;
$double_color_threshold = 20;

// START TIME
$start = microtime(true);

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

// GRAB PARAMETERS
if($_GET['color1'] !== null) {
    $color1 = explode(',', $_GET['color1']);
    // get integer from color cluster GET var
    foreach($color1 as $j => $c){
        $color1[$j] = intval($c);
    }

    // percentage filters only present if color1 exists
    $pmin1 = ($_GET['pmin1'] === null) ? null : intval($_GET['pmin1']);
    $pmax1 = ($_GET['pmax1'] === null) ? null : intval($_GET['pmax1']);

    $color_threshold = ($_GET['threshold'] === null) ? $single_color_threshold : intval($_GET['threshold']); 

    if($_GET['color2'] !== null){
        $color_threshold = ($_GET['threshold'] === null) ? $double_color_threshold : intval($_GET['threshold']); 

        $color2 = explode(',', $_GET['color2']);
        // get integer from color cluster GET var
        foreach($color2 as $j => $c){
            $color2[$j] = intval($c);
        }

        $threshold = 150; // loosen threshold for 2 colors

        $pmin2 = ($_GET['pmin2'] === null) ? null : intval($_GET['pmin2']);
        $pmax2 = ($_GET['pmax2'] === null) ? null : intval($_GET['pmax2']);
    }

    
    
}elseif($_GET['colorscheme'] !== null){
    $colorscheme = strtolower($_GET['colorscheme']);
}

$category = ($_GET['category'] === null) ? null : intval($_GET['category']);
$pattern = ($_GET['pattern'] === null) ? null : strtolower($_GET['pattern']);

// This is the attempt number on the global scale
$major = ($_GET['major'] === null) ? $default_major : intval($_GET['major']);


if($pattern == 'dotted' || $pattern == 'stripes') $threshold = 150; // loosen threshold

$limit = ($_GET['limit'] === null) ? $default_limit : intval($_GET['limit']); // DEFINE LIMIT
$offset = ($_GET['offset'] === null) ? null : intval($_GET['offset']); // DEFINE OFFSET


if($color1 !== null){
    $select_str = 'SELECT items.*';
}else{
    $select_str = 'SELECT *
    FROM items';
}

//CALCULATE DISTANCE FOR MAJOR = 2
if($color1 !== null && False){
    if($color2 === null) {
        // Single Color
        $select_str .= ', SQRT('.$r_coeff.'*POW(ic.R-:R1, 2) + 
                                '.$g_coeff.'*POW(ic.G-:G1, 2) + 
                                '.$b_coeff.'*POW(ic.B-:B1, 2))
                        AS `distance`';
    }else{
        // Double Color
        $select_str .= ', SQRT(POW(SQRT('.$r_coeff.'*POW(ic.R1-:R1, 2) + 
                                '.$g_coeff.'*POW(ic.G1-:G1, 2) + 
                                '.$b_coeff.'*POW(ic.B1-:B1, 2)), 2) +
                            POW(SQRT('.$r_coeff.'*POW(ic.R2-:R2, 2) + 
                                '.$g_coeff.'*POW(ic.G2-:G2, 2) + 
                                '.$b_coeff.'*POW(ic.B2-:B2, 2)), 2)) AS `distance`';
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
$select_str .= ' WHERE';

// FILTER BY CATEGORY
if($category !== null) {
    if($color1 === null) $select_str .= ' category_id = :category AND ';
    else $select_str .= ' items.category_id = :category AND ';
}

// FILTER BY PERCENTAGE(S)
if($color1 !== null){
    if($color2 === null){
        // SINGLE COLOR
        if($pmin1 !== null) $select_str .= ' ic.P >= :pmin1 AND ';
        elseif($pmax1 !== null) $select_str .= ' ic.P <= :pmax1 AND ';
        
        $select_str .= ' ic.R BETWEEN :R1_min AND :R1_max AND ';
        $select_str .= ' ic.G BETWEEN :G1_min AND :G1_max AND ';
        $select_str .= ' ic.B BETWEEN :B1_min AND :B1_max AND ';
        
        
    }else{
        // TWO COLORS
        if($pmin1 !== null) $select_str .= ' ic.P1 >= :pmin1 AND ';
        elseif($pmax1 !== null) $select_str .= ' ic.P1 <= :pmax1 AND ';

        if($pmin2 !== null) $select_str .= ' ic.P2 >= :pmin2 AND ';
        elseif($pmax2 !== null) $select_str .= ' ic.P2 <= :pmax2 AND ';
        
        $select_str .= ' ic.R1 BETWEEN :R1_min AND :R1_max AND ';
        $select_str .= ' ic.G1 BETWEEN :G1_min AND :G1_max AND ';
        $select_str .= ' ic.B1 BETWEEN :B1_min AND :B1_max AND ';

        $select_str .= ' ic.R2 BETWEEN :R2_min AND :R2_max AND ';
        $select_str .= ' ic.G2 BETWEEN :G2_min AND :G2_max AND ';
        $select_str .= ' ic.B2 BETWEEN :B2_min AND :B2_max AND ';
        
    }
}

if($colorscheme !== null){
    $select_str .= ' '.$colorscheme.' >= \'1\' AND ';
}

if($pattern !== null){
    if($color1 === null) $select_str .= ' '.$pattern.' >= \'1\' AND ';
    else $select_str .= ' items.'.$pattern.' >= \'1\' AND ';
}

$select_str .= '\'1\' = \'1\''; // Neutralizes 'AND's

//HAVING THRESHOLD (COLOR DISTANCE)
// if($color1 !== null && $major == 2) {
//     // $select_str .= ' HAVING distance < :threshold ';
// }

// ORDER WITH COLOR CLUSTERS
if($color1 !== null){
    // $double_color_percent = ($color2 === null) ? '' : '1';
    // $select_str .= ' ORDER BY';
    if($pattern !== null) $select_str .= ' ORDER BY items.'.$pattern.' DESC';
    // else $select_str .= ' ic.P'.$double_color_percent.' DESC'; 
}else if($color1 === null){
    $select_str .= ' ORDER BY ';
    if($colorscheme !== null) $select_str .= $colorscheme.' DESC';
    if($pattern !== null) {
        if($colorscheme) $select_str .= ', ';
        $select_str .= $pattern.' DESC';
    }
}

if($limit !== null) $select_str .= ' LIMIT :lim'; // ADD LIMIT TO QRY
if($offset !== null) $select_str .= ' OFFSET :offset'; // ADD OFFSET TO QRY


// SET NEXT & LAST LINKS

if($offset === null) {
    $next = ($_GET['limit'] === null) ? 
        $_SERVER[REQUEST_URI].'&limit='.$limit.'&offset='.$limit : 
        preg_replace('/limit=[0-9]+/', 'limit='.$limit.'&offset='.$limit, $_SERVER[REQUEST_URI]);
} else {
    $lim_offset_diff = $offset - $limit;
    if($lim_offset_diff == 0) $last = preg_replace('/&offset=[0-9]+/', '', $_SERVER[REQUEST_URI]);
    if ($lim_offset_diff > 0) $last = preg_replace('/offset=[0-9]+/', 'offset='.($offset-$limit), $_SERVER[REQUEST_URI]);
    $next = preg_replace('/offset=[0-9]+/', 'offset='.($offset+$limit), $_SERVER[REQUEST_URI]);
}




// print $select_str;

$select = $pdo->prepare($select_str);

do{
    // BIND PARAMS
    if($color1 !== null){
        
        if($color2 !== null) {
            // Double Color

            $select->bindParam(':R2_min', $a=$color2[0]-$color_threshold, PDO::PARAM_INT);
            $select->bindParam(':G2_min', $a=$color2[1]-$color_threshold, PDO::PARAM_INT);
            $select->bindParam(':B2_min', $a=$color2[2]-$color_threshold, PDO::PARAM_INT);
            $select->bindParam(':R2_max', $a=$color2[0]+$color_threshold, PDO::PARAM_INT);
            $select->bindParam(':G2_max', $a=$color2[1]+$color_threshold, PDO::PARAM_INT);
            $select->bindParam(':B2_max', $a=$color2[2]+$color_threshold, PDO::PARAM_INT);
        }

        $select->bindParam(':R1_min', $a=$color1[0]-$color_threshold, PDO::PARAM_INT);
        $select->bindParam(':G1_min', $a=$color1[1]-$color_threshold, PDO::PARAM_INT);
        $select->bindParam(':B1_min', $a=$color1[2]-$color_threshold, PDO::PARAM_INT);
        $select->bindParam(':R1_max', $a=$color1[0]+$color_threshold, PDO::PARAM_INT);
        $select->bindParam(':G1_max', $a=$color1[1]+$color_threshold, PDO::PARAM_INT);
        $select->bindParam(':B1_max', $a=$color1[2]+$color_threshold, PDO::PARAM_INT);
    }

    // PERCENTAGE
    if($pmin1 !== null) $select->bindParam(':pmin1', $pmin1);
    elseif($pmax1 !== null) $select->bindParam(':pmax1', $pmax1);
    if($pmin2 !== null) $select->bindParam(':pmin2', $pmin2);
    elseif($pmax2 !== null) $select->bindParam(':pmax2', $pmax2);

    // OTHER CONSTRAINTS
    if($category !== null) $select->bindParam(':category', $category, PDO::PARAM_STR); // BIND CATEGORY ID
    if($limit !== null) $select->bindParam(':lim', $limit, PDO::PARAM_INT); // BIND LIMIT VAL
    if($offset !== null) $select->bindParam(':offset', $offset, PDO::PARAM_INT); // BIND OFFSET VAL


    // EXECUTION + DATA RETRIEVAL
    try{
        $select->execute();
        $result = $select->fetchAll(PDO::FETCH_ASSOC);

        $count = sizeof($result);
        
        if($count >= $limit){
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

            $time_elapsed = round(microtime(true) - $start, 3); 

            $output = array('meta' => array(
                'query' => $select_str,
                'count' => sizeof($result),
                'limit' => $limit,
                'offset' => $offset,
                'time_elapsed' => $time_elapsed
            ),
            'data' => $result);

            if($color_threshold !== null) {
                if($_GET['threshold'] === null){
                    if($next !== null) $next .= '&threshold='.$color_threshold;
                    if($last !== null) $last .= '&threshold='.$color_threshold;
                }
                $output['meta']['color_threshold'] = $color_threshold;
            }

            $output['meta']['self'] = 'http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
            if($next !== null) $output['meta']['next'] = 'http://'.$_SERVER[HTTP_HOST].$next;
            if($last !== null) $output['meta']['last'] = 'http://'.$_SERVER[HTTP_HOST].$last;


            // FORMAT OUTPUT
            header("HTTP/1.1 200 OK");
            header('Content-Type: application/json');
            print(json_encode($output));
        }else{
            $color_threshold += ($color2 === null) ? 15 : 40;
        }

    }catch(\PDOException $ex){
        // ERROR OUTPUT
        header("HTTP/1.1 422 OK");
        header('Content-Type: application/json');
        $output = array(
            'query' => $select_str,
            'error'=> $ex->getMessage()
        );
        print(json_encode($output));
        exit();
    }


}while(sizeof($result) < $limit);

?> 