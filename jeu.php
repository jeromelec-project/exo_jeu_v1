<?php session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/fonctions.php';

$prenom = $_SESSION['prenom'];
$id_client = $_SESSION['id_client'] ?? null;
$message = $_SESSION['message'] ?? null;
$message_conseil = $_SESSION['message_conseil'] ?? null;

if (is_array($id_client)) {
    $id_client = $id_client['id_client'] ?? null;
}

if (!is_scalar($id_client) || (string) $id_client === '') {
    header('Location: index.php');
    exit;
}

$_SESSION['id_client'] = $id_client;

$player = getPlayerData($pdo, $id_client);



$jours_restants = getJours($pdo, $id_client);   

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>

<div class="header">
    <span class="message">
        <?php
        if ($message) {
            echo "<h1>$message</h1>";
            unset($_SESSION['message']);

        } else {
            echo "<h1>Bonjour " . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . " et bienvenue sur le jeu</h1>";
        }
        ?>
    </span>
</div>

<?php
$statut_jeu = getPlayerData($pdo, $id_client)['statut_jeu'] ?? null;
// var_dump($statut_jeu);
if ($statut_jeu === 'gagne' || $statut_jeu === 'perdu') {
    echo "<div class='bouton_fin'><button><a href='./index.php'>Quitter</a></button></div>";
} else {
?>

    <div class="jours_restants">
        <h2>Il reste <?php echo $jours_restants; ?> jours</h2>
    </div>


    <div class="joueur_stats">
        <?php 
            if ($player) {
        ?>

        <div class='stat_bar'>

            <div class='barres'>
                Santé : <?php echo $player['sante']; ?>
                <div class='barre_parent'>
                    <div class='barre_sante' style='width: <?php echo $player['sante']; ?>%;'></div>
                </div>
                ❤️
            </div>

            <div class='barres'>
                Énergie : <?php echo $player['energie']; ?>
                <div class='barre_parent'>
                    <div class='barre_energie' style='width: <?php echo $player['energie']; ?>%;'></div>
                </div>
                ⚡
            </div>

            <div class='barres'>
                Faim : <?php echo $player['faim']; ?>
                <div class='barre_parent'>
                    <div class='barre_faim' style='width: <?php echo $player['faim']; ?>%;'></div>
                </div>
                🍕
            </div>

            <div class='barres'>
                Matériaux : <?php echo $player['materiaux']; ?>
                <div class='barre_parent'>
                    <div class='barre_materiaux' style='width: <?php echo $player['materiaux']; ?>%;'></div>
                </div>
                📦
            </div>

            <div class='barres'>
                Maison : <?php echo $player['maison']; ?>
                <div class='barre_parent'>
                    <div class='barre_maison' style='width: <?php echo $player['maison']; ?>%;'></div>
                </div>
                🏠
            </div>

            <?php            
                } else {
                    echo "Aucun joueur trouve.";
                }
            ?>
        </div>

        <div class="etat_maison">
            <?php $maison_upper = de20en20($player['maison']); ?>
            <img src="./style/maison_png/etat_<?php echo $maison_upper; ?>.png" width="300px" alt="maison">
        </div>

    </div>



    <div class="actions">
        <h2>Actions</h2>
        <form action="./traitement_actions.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" name="action" value="manger">Manger</button>
            <button type="submit" name="action" value="dormir">Dormir</button>
            <button type="submit" name="action" value="farmer">Farmer</button>
            <button type="submit" name="action" value="reparer">Réparer</button>
        </form>

    <!-- <p>
    (Le but est de survivre 30 jours. Une action = 1 jour) <br>
    </p> -->

    <div class="conseil">
        <?php
            if ($message_conseil) {
        ?>
            <p>
                <?php echo htmlspecialchars($message_conseil, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php
            unset($_SESSION['message_conseil']);
        }
        ?>
    </div>

<?php } ?>

</body>
</html>