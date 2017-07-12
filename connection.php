<?php
// Load Environment Variables from .env
require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$host = getenv('MYSQL_HOST');
$user = getenv('MYSQL_USERNAME');
$password = getenv('MYSQL_PASSWORD');
$db = getenv('MYSQL_DATABASE');
$port = getenv('MYSQL_PORT');

try{
    $pdo = new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$db.';charset=utf8mb4',
                        $user,
                        $password,
                        array(
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_PERSISTENT => false
                        )
                    );
}
catch(\PDOException $ex){
    print($ex->getMessage());
}

?>
