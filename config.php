<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host     = "localhost";
$dbname   = "pastel_wear";
$username = "root";
$password = "root";  // <--- mets ici TON vrai mot de passe MySQL

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
