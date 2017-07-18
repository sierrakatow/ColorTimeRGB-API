<?php
// Load Environment Variables from .env
require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$host = getenv('MYSQL_HOST2');
$user = getenv('MYSQL_USERNAME2');
$password = getenv('MYSQL_PASSWORD2');
$db = getenv('MYSQL_DATABASE2');
$port = getenv('MYSQL_PORT2');

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
