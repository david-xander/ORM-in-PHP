<?php

$host = 'db';
$db   = 'david';
$user = 'root';
$pass = 'root';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

$SQL='
    CREATE TABLE IF NOT EXISTS cosa2
    (
        id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR( 50 ) NOT NULL
    )
';
$pdo->exec($SQL);

$SQL = 'INSERT INTO cosa2 (nombre) VALUES (:nombre)';
$stm=$pdo->prepare($SQL);
$stm->execute(["nombre"=>"raq m"]);

$stmt = $pdo->query('SELECT * FROM cosa2');
while ($row = $stmt->fetch())
{
    echo "{$row['nombre']}<br>";
}

?>