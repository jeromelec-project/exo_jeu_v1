<?php
// if ($action ==='reparation') {

//     $sql = "UPDATE partie
//     SET etat_abri = LEAST(etat_abri + 20, 100)
//     WHERE id = :partie_id";


// $stmt = $pdo->execute([ 
//     'partie_id' => $partieId
// ]);

// $_SESSION['message'] = "votre abri a été réparé !";


// }
?>
<!-- faire attention aux -> XSS (cross site scripting), Injections SQL, injection JS, CSRF ( cross site request forgery) -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Site externe</title>
</head>
<body>


    <h1>Site externe</h1>


    <form action="http://localhost/exo_jeu/traitement_actions.php"
          method="POST">


        <input type="hidden" name="action" value="manger">


        <button type="submit">
            Déclencher une action
        </button>


    </form>


</body>
</html>