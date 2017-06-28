<?php

function getCategories($pdo){
    return $pdo->query('SELECT * FROM categories ORDER BY id DESC');
}

function getCategoriesMap($pdo){
    $map = array();
    $rows = getCategories($pdo);
    foreach($rows as $r){
        $map[$r['name']] = $r['id'];
    }
    return $map;
}

?>