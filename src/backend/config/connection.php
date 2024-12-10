<?php

$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_DBNAME'];

$conn = "";

// Ejemplo de conexión PDO
try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbname", $dbUser, $dbPass);

    $conn->exec("SET NAMES utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
