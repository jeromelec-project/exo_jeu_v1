<?php

function de20en20($input) {
    if ($input == 0) {
        return 0;
    } elseif ($input < 20 || $input == 20){
        return 20;
    } elseif ($input < 40 || $input == 40){
        return 40;
    } elseif ($input < 60 || $input == 60){
        return 60;
    } elseif ($input < 80 || $input == 80){
        return 80;
    } else {
        return 100;
}
}



function getPlayerData($pdo, $id_client) {
    $sql = "SELECT id_client, prenom, sante, energie, faim, materiaux, statut_jeu, maison FROM client WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_client' => $id_client]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function getJours($pdo, $id_client) {
    $sql = "SELECT GREATEST(0, 30 - GREATEST(0, TIMESTAMPDIFF(DAY, CURRENT_TIMESTAMP, date_creation))) AS jours_restants
            FROM client
            WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_client' => $id_client]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return (int) $result['jours_restants'];
}

function updatePlayerData($pdo, $id_client, $sante, $energie, $faim, $materiaux, $statut_jeu) {
    $sql = "UPDATE client SET sante = :sante, energie = :energie, faim = :faim, materiaux = :materiaux, statut_jeu = :statut_jeu WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sante' => $sante, 'energie' => $energie, 'faim' => $faim, 'materiaux' => $materiaux, 'statut_jeu' => $statut_jeu, 'id_client' => $id_client]);
}




function partieStatut($pdo, $id_client) {
    $player = getPlayerData($pdo, $id_client);
    $jours_restants = getJours($pdo, $id_client);
    if ($jours_restants == 0) {
        $_SESSION['message'] = "Vous avez survécu 30 jours ! C'est gagné !";
        updatePlayerData($pdo, $id_client, $player['sante'], $player['energie'], $player['faim'], $player['materiaux'], 'gagne');
        unset($_SESSION['message_conseil']);

    }

    if ($player['sante'] == 0) {
        $_SESSION['message'] = "perdu ! Vous n'avez plus de sante !";
        updatePlayerData($pdo, $id_client, $player['sante'], $player['energie'], $player['faim'], $player['materiaux'], 'perdu');
        unset($_SESSION['message_conseil']);
    }

}





function sante($pdo, $id_client, $sante, $valeur, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($source === 'incrementTime' && $player['maison'] == 0) {
        $valeur = '-20';
    }

    if ($source === 'dormir' && $player['faim'] == 100) {
        $valeur = '-10';
    }
    
    if ($source === 'dormir' && $player['materiaux'] == 0) {
        $valeur = '-10';
    }

    if ($source === 'manger' && $player['materiaux'] == 0) {
        $valeur = '-5';
    }

    $santePlusValeur = $sante + $valeur;
    $santeResultat = max(0, min(100, $santePlusValeur));

    $sql = "UPDATE client SET sante = :sante WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sante' => $santeResultat, 'id_client' => $id_client]);
    
    return $santeResultat;

}

function energie($pdo, $id_client, $energie, $valeur, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($source === 'incrementTime' && $player['maison'] == 0) {
        $valeur = '-30';
    }

    if ($source === 'manger' && $player['materiaux'] == 0) {
        $valeur = '-20';
    }

    if ($source === 'dormir' && $player['materiaux'] == 0) {
        $valeur = '-5';
    }

    if ($source === 'dormir' && $player['faim'] == 100) {
        $valeur = '-10';
    }
    
    $energiePlusValeur = $energie + $valeur;
    $energieResultat = max(0, min(100, $energiePlusValeur));

    $sql = "UPDATE client SET energie = :energie WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['energie' => $energieResultat, 'id_client' => $id_client]);
    
    return $energieResultat;
}


function faim($pdo, $id_client, $faim, $valeur, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($source === 'manger' && $player['materiaux'] == 0) {
        $valeur = '0';
    }

    if ($source === 'farmer' && $player['faim'] == 100) {
        $valeur = '5';
    }

    $faimPlusValeur = $faim + $valeur;
    $faimResultat = max(0, min(100, $faimPlusValeur));

    $sql = "UPDATE client SET faim = :faim WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['faim' => $faimResultat, 'id_client' => $id_client]);
    
    return $faimResultat;
}


function materiaux($pdo, $id_client, $materiaux, $valeur, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($source === 'farmer')   {
        if ($player['energie'] == 0 && $player['faim'] == 100) {
            $valeur = $valeur / 12;
        } elseif ($player['energie'] == 0 && $player['faim'] != 100) {
            $valeur = $valeur / 4;
        } 
    }

    $materiauxPlusValeur = $materiaux + $valeur;
    $materiauxResultat = max(0, min(100, $materiauxPlusValeur));

    $sql = "UPDATE client SET materiaux = :materiaux WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['materiaux' => $materiauxResultat, 'id_client' => $id_client]);
    
    return $materiauxResultat;
}

function maison($pdo, $id_client, $maison, $valeur, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($player['materiaux'] == 0) {
        $valeur = '-5';
    }

    $maisonPlusValeur = $maison + $valeur;
    $maisonResultat = max(0, min(100, $maisonPlusValeur));

    $sql = "UPDATE client SET maison = :maison WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['maison' => $maisonResultat, 'id_client' => $id_client]);
    
    return $maisonResultat;
}






function messageConseil($pdo, $id_client, $source) {
    $player = getPlayerData($pdo, $id_client);

    if ($source === 'manger' && $player['materiaux'] == 0) {
        $_SESSION['message_conseil'] = "Vous n'avez plus de materiaux. Vous ne pouvez pas manger.";
    }

    if ($source === 'farmer' && $player['faim'] == 100) {
        $_SESSION['message_conseil'] = "Vous avez faim. Vous farmez vraiment peu.";        
    }

    if ($source === 'reparer' && $player['materiaux'] == 0) {
        $_SESSION['message_conseil'] = "Vous n'avez plus de materiaux. Vous ne pouvez pas réparer votre maison.";
    }

    if ($source === 'incrementTime' && $player['maison'] == 0) {
        $_SESSION['message_conseil'] = "Vous n'avez plus de maison. vous perdez de la sante.";
    }

    if ($source === 'dormir' && $player['faim'] == 100) {
        $_SESSION['message_conseil'] = "Vous avez faim. Vous dormez mal.";
    }

    if ($source === 'dormir' && $player['materiaux'] == 0) {
        $_SESSION['message_conseil'] = "Vous n'avez plus de materiaux. Vous dormez mal.";
    }

    if ($source === 'manger' && $player['materiaux'] == 0) {
        $_SESSION['message_conseil'] = "Vous n'avez plus de materiaux. Vous ne pouvez pas manger.";
    }


    if ($source === 'farmer') {
    if ($player['energie'] == 0 && $player['faim'] == 100) {
        $_SESSION['message_conseil'] = "Vous n'avez plus d'énergie et vous avez faim. vous ne farmer presque plus. Vous perdez de la sante.";
    } elseif ($player['energie'] == 0 && $player['faim'] != 100) {
        $_SESSION['message_conseil'] = "Vous n'avez plus d'énergie. Vous farmer moins.";
    } 
    }

}



function incrementTime($pdo, $id_client, $source) {
    $player = getPlayerData($pdo, $id_client);

    maison($pdo, $id_client, $player['maison'], '-5', 'incrementTime');

    if ($player['maison'] == 0) {
        sante($pdo, $id_client, $player['sante'], null, 'incrementTime');
        energie($pdo, $id_client, $player['energie'], null, 'incrementTime');
    }

    $sql = "UPDATE client SET date_creation = DATE_ADD(date_creation, INTERVAL 1 DAY) WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_client' => $id_client]);

    messageConseil($pdo, $id_client, $source);
    partieStatut($pdo, $id_client);
}








function manger($pdo, $id_client) {
    $player = getPlayerData($pdo, $id_client);

    $sante = sante($pdo, $id_client, $player['sante'], '20', 'manger');
    $energie = energie($pdo, $id_client, $player['energie'], '15', 'manger');
    $faim = faim($pdo, $id_client, $player['faim'], '-35', 'manger');
    $materiaux = materiaux($pdo, $id_client, $player['materiaux'], '-3', 'manger');
    updatePlayerData($pdo, $id_client, $sante, $energie, $faim, $materiaux, $player['statut_jeu']);

    incrementTime($pdo, $id_client, 'manger');
}



function dormir($pdo, $id_client) {
    $player = getPlayerData($pdo, $id_client);

    $sante = sante($pdo, $id_client, $player['sante'], '10', 'dormir');
    $energie = energie($pdo, $id_client, $player['energie'], '20', 'dormir');
    $faim = faim($pdo, $id_client, $player['faim'], '5', 'dormir');
    $materiaux = materiaux($pdo, $id_client, $player['materiaux'], '-2', 'dormir');
    updatePlayerData($pdo, $id_client, $sante, $energie, $faim, $materiaux, $player['statut_jeu']);

    incrementTime($pdo, $id_client, 'dormir');
}


function farmer($pdo, $id_client) {
    $player = getPlayerData($pdo, $id_client);

    $sante = sante($pdo, $id_client, $player['sante'], '-15', 'farmer');
    $energie = energie($pdo, $id_client, $player['energie'], '-25', 'farmer');
    $faim = faim($pdo, $id_client, $player['faim'], '25', 'farmer');
    $materiaux = materiaux($pdo, $id_client, $player['materiaux'], '30', 'farmer');
    updatePlayerData($pdo, $id_client, $sante, $energie, $faim, $materiaux, $player['statut_jeu']);

    incrementTime($pdo, $id_client, 'farmer');
} 


function reparer($pdo, $id_client) {
    $player = getPlayerData($pdo, $id_client);

    $sante = sante($pdo, $id_client, $player['sante'], '-5', 'reparer');
    $energie = energie($pdo, $id_client, $player['energie'], '-25', 'reparer');
    $faim = faim($pdo, $id_client, $player['faim'], '25', 'reparer');
    $materiaux = materiaux($pdo, $id_client, $player['materiaux'], '-30', 'reparer');
    $maison = maison($pdo, $id_client, $player['maison'], '30', 'reparer');
    updatePlayerData($pdo, $id_client, $sante, $energie, $faim, $materiaux, $player['statut_jeu']);

    incrementTime($pdo, $id_client, 'reparer');
}