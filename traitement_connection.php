<?php
session_start();
require_once __DIR__ . '/database.php';

$prenom = trim($_POST['prenom'] ?? '');
$prenom = htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8');


if ($prenom === '') {
    exit ("veuilez remplir le champ prénom");
}

$sql = "INSERT INTO client (prenom) VALUE (:prenom)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['prenom' => $prenom]);
$player_id = $pdo->lastInsertId();

if ($prenom === '') {
    exit ("veuilez remplir le champ prenom");
} else {
    $_SESSION['id_client'] = $player_id;
    $_SESSION['prenom'] = $prenom;
    header('Location: jeu.php');
    exit;
}

?>