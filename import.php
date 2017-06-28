<?php

include('./connection.php');
include('./functions.php');

$input_file = './assets/'.$argv[1];

$categories = getCategoriesMap($pdo);

if (($file = fopen($input_file, "r")) !== FALSE) {
    $i = 1;
    while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
        $num = count($data);
        $i++;
    
        $insert_sql = $pdo->prepare('INSERT INTO items 
            SET `link` = :link,
            `img_url` = :img_url,
            `price` = :price,
            `category_id` = :category_id,
            `serial_number` = :serial_number,
            `R1` = :R1, `G1` = :G1, `B1` = :B1, `P1` = :P1,
            `R2` = :R2, `G2` = :G2, `B2` = :B2, `P2` = :P2,
            `R3` = :R3, `G3` = :G3, `B3` = :B3, `P3` = :P3,
            `R4` = :R4, `G4` = :G4, `B4` = :B4, `P4` = :P4');

        $insert_sql->bindParam(':link', $data[0], PDO::PARAM_STR);
        $insert_sql->bindParam(':img_url', $data[1], PDO::PARAM_STR);
        $insert_sql->bindParam(':price', $data[2], PDO::PARAM_STR);
        $insert_sql->bindParam(':category_id', $categories[$data[3]], PDO::PARAM_INT);
        $insert_sql->bindParam(':serial_number', $data[4], PDO::PARAM_STR);
        $insert_sql->bindParam(':R1', $data[5], PDO::PARAM_INT);
        $insert_sql->bindParam(':G1', $data[6], PDO::PARAM_INT);
        $insert_sql->bindParam(':B1', $data[7], PDO::PARAM_INT);
        $insert_sql->bindParam(':P1', $data[8], PDO::PARAM_INT);
        $insert_sql->bindParam(':R2', $data[9], PDO::PARAM_INT);
        $insert_sql->bindParam(':G2', $data[10], PDO::PARAM_INT);
        $insert_sql->bindParam(':B2', $data[11], PDO::PARAM_INT);
        $insert_sql->bindParam(':P2', $data[12], PDO::PARAM_INT);
        $insert_sql->bindParam(':R3', $data[13], PDO::PARAM_INT);
        $insert_sql->bindParam(':G3', $data[14], PDO::PARAM_INT);
        $insert_sql->bindParam(':B3', $data[15], PDO::PARAM_INT);
        $insert_sql->bindParam(':P3', $data[16], PDO::PARAM_INT);
        $insert_sql->bindParam(':R4', $data[17], PDO::PARAM_INT);
        $insert_sql->bindParam(':G4', $data[18], PDO::PARAM_INT);
        $insert_sql->bindParam(':B4', $data[19], PDO::PARAM_INT);
        $insert_sql->bindParam(':P4', $data[20], PDO::PARAM_INT);

        try{
            $insert_sql->execute();

            $item_id = $pdo->lastInsertId();

            for($colorscheme_id=1;$colorscheme_id<=7;$colorscheme_id++){
                $value = $data[20+$colorscheme_id];
                if($value > 0){
                    $schemes = $pdo->prepare('INSERT INTO items_colorschemes
                        SET `item_id` = :item_id,
                        `colorscheme_id` = :colorscheme_id,
                        `value` = :value');
                    $schemes->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                    $schemes->bindParam(':colorscheme_id', $colorscheme_id, PDO::PARAM_INT);
                    $schemes->bindParam(':value', $value, PDO::PARAM_INT);
                    $schemes->execute();
                }
            }

            for($pattern_id=1;$pattern_id<=2;$pattern_id++){
                $value = $data[27+$pattern_id];
                if($value > 0){
                    $patterns = $pdo->prepare('INSERT INTO items_patterns
                        SET `item_id` = :item_id,
                        `pattern_id` = :pattern_id,
                        `value` = :value');
                    $patterns->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                    $patterns->bindParam(':pattern_id', $pattern_id, PDO::PARAM_INT);
                    $patterns->bindParam(':value', $value, PDO::PARAM_INT);
                    $patterns->execute();
                }
            }


        }catch(\PDOException $ex){
            print($ex->getMessage());
            echo "\n";
            // $insert_sql->debugDumpParams();
        }
    }
    fclose($file);
}

?>