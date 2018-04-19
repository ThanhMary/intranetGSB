<?php


$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if ( ! estVisiteurConnecte() ) {
    header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");

$idVisiteur =  lireDonneeUrl('id', "");
$etape=lireDonnee("etape","affichageFiche");
$idLigneHF = lireDonneeUrl('idLigneHF', "");
$libelleLigneHF = lireDonneeUrl('libelle', "");
$tabQteEltsForfait=lireDonneePost("txtEltsForfait", "");

$tabErreurs;

//Ancienne methode pour avoir la date
$date = lireDonneeUrl('date','');

$moisDernier = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
$moisDernier = date('Ym', $moisDernier);

if( $etape == 'affichageFiche'){
  $fichesForfait = obtenirFicheFraisForfaits($idConnexion, $idVisiteur, $moisDernier);
  $reqFichesHorsForfait = obtenirReqEltsHorsForfaitFicheFrais($idConnexion, $moisDernier, $idVisiteur);
}else{
  $fichesForfait = obtenirFicheFraisForfaits($idConnexion, $idVisiteur, $date);
  $reqFichesHorsForfait = obtenirReqEltsHorsForfaitFicheFrais($idConnexion, $date, $idVisiteur);
}

$totalForfait = 0;
$totalHorsForfait = 0;
$visiteur = obtenirDetailVisiteur($idConnexion, $idVisiteur);

$etape=lireDonnee("etape","affichageFiche");

if ($etape == "refuserLigneHF"){
  if($libelleLigneHF != '' && $idLigneHF != ''){
      refuserLigneHF($idConnexion, $idLigneHF, $libelleLigneHF );
      header('Location: cAffichageFicheFrais.php?etape=affichageFiche&id='.$idVisiteur);
  }
}

if ($etape == "validerLigneHF"){
  if($libelleLigneHF != '' && $idLigneHF != ''){
      validerLigneHF($idConnexion, $idLigneHF, $libelleLigneHF );
      header('Location: cAffichageFicheFrais.php?etape=affichageFiche&id='.$idVisiteur);
      
  }
}

if($etape == 'ficheValide'){
  validerFicheFrais($idConnexion, $idVisiteur);
  header('Location: cAffichageFicheFrais.php?etape=affichageFiche&id='.$idVisiteur);
}

if($etape == 'miseEnPaiementOK'){
  
  mettreEnPaiementFicheFrais($idConnexion, $idVisiteur, $date);
  header('Location: cAffichageFicheFrais.php?etape=miseEnPaiement&id='.$idVisiteur.'&date='.$date);
}


?>

<div id='contenu' >
  <?php
  if ($etape == 'affichageFiche' || $etape == 'modificationFiche'){
    ?>
  
    <h2> Validation des fiches de frais de <?php echo $visiteur['nom'].' '.$visiteur['prenom'].' pour '.obtenirLibelleMois(intval(substr($moisDernier,4,2))).' '.substr($moisDernier,0,4); ?> </h2>
    
    
    <?php
  }
  
  if ($etape == 'miseEnPaiement'){
    ?>
    <h2> Mise en paiement des fiches de frais de <?php echo $visiteur['nom'].' '.$visiteur['prenom'].' pour '.obtenirLibelleMois(intval(substr($date,4,2))).' '.substr($date,0,4); ?> </h2>
    
    <?php
  }
  ?>
  
  <h3> Frais Forfaitisé</h3>
    
  <?php
   if($etape == "modificationFiche"){
      ?>
    <!--?etape=affichageFiche&amp;id=<?php // echo $idVisiteur ;?>-->
    <form action="" method="post">
      <div class="corpsForm">
        <input type="hidden" name="etape" value="validerSaisie" />
          <fieldset>
            <legend>Eléments forfaitisés</legend>
            <?php
    
            $req = obtenirReqEltsForfaitFicheFrais($idConnexion, $moisDernier, $idVisiteur);
            $infoFicheFraisForfait = mysqli_query($idConnexion, $req);
            $ficheFraisForfait = mysqli_fetch_assoc($infoFicheFraisForfait);
            while ( is_array($ficheFraisForfait) ) {
              $idFraisForfait = $ficheFraisForfait["idFraisForfait"];
              $libelle = $ficheFraisForfait["libelle"];
              $quantite = $ficheFraisForfait["quantite"];
              ?>
              <p>
                <label for="<?php echo $idFraisForfait ?>">* <?php echo $libelle; ?> : </label>
                <input type="text" id="<?php echo $idFraisForfait ?>"
                       name="txtEltsForfait[<?php echo $idFraisForfait ?>]"
                       size="10" maxlength="5"
                       title="Entrez la quantité de l'élément forfaitisé"
                       value="<?php echo $quantite; ?>" />
              </p>
              <?php
              $ficheFraisForfait = mysqli_fetch_assoc($infoFicheFraisForfait);
            }
            mysqli_free_result($infoFicheFraisForfait);
            
            ?>
          </fieldset>
        </div>
        <div class="piedForm">
          <p>
            <input id="ok" type="submit" value="Valider" size="20"
                   title="Enregistrer les nouvelles valeurs des éléments forfaitisés" />
          </p>
        </div>
    </form>
  
    <?php
    $tabQteEltsForfait=lireDonneePost("txtEltsForfait", "");
    if($_POST){
      $ok = verifierEntiersPositifs($tabQteEltsForfait);
      if (!$ok) {
          ajouterErreur($tabErreurs, "Chaque quantité doit être renseignée et numérique positive.");
          echo toStringErreurs($tabErreurs);
          //header('Location: cAffichageFicheFrais.php?etape=modificationFiche&id='.$idVisiteur);
      }
      else { // mise à jour des quantités des éléments forfaitisés
          modifierEltsForfait($idConnexion, $moisDernier, $idVisiteur, $tabQteEltsForfait);
          header('Location: cAffichageFicheFrais.php?etape=affichageFiche&id='.$idVisiteur);
      }
    }
  }
  
    
  if($etape == 'affichageFiche' || $etape == 'miseEnPaiement'){
  ?>
  
    <table class ="listeLegere">
      <tr>
        <th class="qteForfait"> Frais Forfait </th>
        <th class="qteForfait"> Quantité </th>
        <th class="qteForfait"> Montant unitaire </th>
        <th class="qteForfait"> Total </th>
      </tr>
      
      <?php
      foreach ($fichesForfait as $fiche){
        $frais = obtenirFraisForfait($idConnexion, $fiche['frais']);

        $totalForfait += $fiche['quantite'] * $frais;
        $libelleEtat = obtenirLibelleEtat($idConnexion, $fiche['etat']);
        $etat = $fiche['etat'];
        ?>
        <tr>
          <?php
            echo '<td>'.$fiche['libelle'].'</td>';
            echo '<td>'.$fiche['quantite'].'</td>';
            echo '<td>'.$fiche['montant'].'</td>';
            echo '<td>'.$fiche['quantite']*$frais.' € </td>';
          ?>
        </tr>
        <?php
      }
      ?>
    
    </table>
    <?php
    if ($etape == 'affichageFiche'){
      ?>
    
	<h2>   <center> <a href="?etape=modificationFiche&amp;id=<?php echo $idVisiteur; ?>"
         onclick="return confirm('Voulez-vous vraiment modifier le contenu de cette fiche ?');"
         title="Modifier la fiche de frais">MODIFIER</a></center></h2>
      <?php
    }
    ?>
    
    
   <h3> Frais Hors Forfait </h3>

    <table class ="listeLegere">
      <tr>
        <th class"qteForfait"> Libelle </th>
        <th class"qteForfait"> Date </th>
        <th class"qteForfait"> Montant </th>
        <?php
        if ( $etape == 'affichageFiche'){
          ?>
          <th class="action">&nbsp;</th>
          <?php
        }
        ?>
         
      </tr>
      <?php
      //Affichage des fiches de frais non forfaitiser du visiteur 
      
      $fichesHorsForfait = mysqli_query($idConnexion, $reqFichesHorsForfait);
      $ficheHorsForfait = mysqli_fetch_assoc($fichesHorsForfait);
          
      while (is_array($ficheHorsForfait)){
       
        ?>
        <tr>
          <td><?php echo $ficheHorsForfait['libelle']; ?> </td>
          <td><?php echo $ficheHorsForfait['date']; ?> </td>
          <td><?php echo $ficheHorsForfait['montant']; ?> €</td>
              
          <?php
          if( $etape == 'affichageFiche'){
            if(substr($ficheHorsForfait['libelle'], 0, 6) == 'REFUSE' ){
              ?>
              <td><a href="?id=<?php echo $idVisiteur; ?>&amp;etape=validerLigneHF&amp;idLigneHF=<?php echo $ficheHorsForfait["id"];?>&amp;libelle=<?php echo $ficheHorsForfait['libelle'];?>"
                     onclick="return confirm('Voulez-vous vraiment valider cette ligne de frais hors forfait ?');"
                     title="Valider la ligne de frais hors forfait">Valider</a></td>
              <?php
            }
            else{
              ?>
              <td><a href="?id=<?php echo $idVisiteur; ?>&amp;etape=refuserLigneHF&amp;idLigneHF=<?php echo $ficheHorsForfait["id"];?>&amp;libelle=<?php echo $ficheHorsForfait['libelle'];?>"
                     onclick="return confirm('Voulez-vous vraiment annuler cette ligne de frais hors forfait ?');"
                     title="refuser la ligne de frais hors forfait">Refuser</a></td>
              <?php
              $totalHorsForfait += $ficheHorsForfait['montant'];
            }
          }else{
            $totalHorsForfait += $ficheHorsForfait['montant'];
          }
          ?>
          
        </tr>
        <?php
        $ficheHorsForfait = mysqli_fetch_assoc($fichesHorsForfait);
      }
      ?>
    </table>
     
    <h3> Total Forfaitisé : <?php echo $totalForfait; ?> € </h3>
    <h3> Total Hors Forfait : <?php echo $totalHorsForfait; ?> €</h3>
    <h3> Total Frais : <?php echo $totalForfait+$totalHorsForfait; ?> € </h3>
    
    <?php
    if( $etape == 'affichageFiche'){
      if($etat == 'CL'){
        ?>
   <h2><center><a href=cValidationFicheFrais.php? > RETOUR </a>  <a href=?id=<?php echo $idVisiteur ?>&etape=ficheValide> VALIDER </a> </center></h2>
	
        <?php
      }
      
      if ($etat == 'VA'){
        ?>
        <h2> <a href=cValidationFicheFrais.php? > RETOUR </a> </h2>
        <p class="info"> FICHE VALIDEE</p>   
        <?php
      }
    }
 
    if ($etape == 'miseEnPaiement'){
      if ($etat == 'VA'){
        ?>
        <h2> <a href='?etape=miseEnPaiementOK&amp;id=<?php echo $idVisiteur ?>&amp;date=<?php echo $date ?>'> MISE EN PAIEMENT </a></h2>
        <h2> <a href=cSuiviPaiement.php? > RETOUR </a> </h2>
        <?php
      }
      if ($etat == 'MP'){
        ?>
        <h2> <a href=cSuiviPaiement.php? > RETOUR </a> </h2>
        <p class="info"> FICHE MISE EN PAIEMENT</p>
        <?php
      }
    }
  }
  ?>
</div>




<?php
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?>
