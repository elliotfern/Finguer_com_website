<?php

$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_DBNAME'];

$conn = "";
  
try {
    $servername = $dbHost;
    $dbname = $dbname;
    $username = $dbUser;
    $password = $dbPass;
   
    $conn = new PDO(
        "mysql:host=$servername; dbname=$dbname;",
        $username, $password
    );

    // Establecer la codificación de caracteres después de establecer la conexión
    $conn->exec("SET NAMES utf8");
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
} catch(PDOException $e) {
    echo "Connection failed: " 
        . $e->getMessage();
}

?>