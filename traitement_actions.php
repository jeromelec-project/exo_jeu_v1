<?php

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')
        ) {
        die('Connection refusée : jeton CSRF invalide.');
    }
         
}
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/fonctions.php';

$id_client = $_SESSION['id_client'] ?? null;

$action = $_POST['action'] ?? null;
$action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');


if ($action === 'manger') {
    manger($pdo, $id_client);
} elseif ($action === 'dormir') {
    dormir($pdo, $id_client);
} elseif ($action === 'farmer') {
    farmer($pdo, $id_client);
}elseif ($action === 'reparer') {
    reparer($pdo, $id_client);
}


header('Location: jeu.php');

?>