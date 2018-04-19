<?php
/**
 * Script de contrôle et d'affichage du cas d'utilisation "Consulter une fiche de frais"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");

  // page inaccessible si visiteur non connecté
  if ( ! estVisiteurConnecte() ) {
      header("Location: cSeConnecter.php");
  }
  require($repInclude . "_entete.inc.html");
  require($repInclude . "_sommaire.inc.php");
  
  //acienne methode pour avoir la date
  $date = sprintf("%04d%02d", date("Y"), date("m")-1);
  $moisDernier = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
  $moisDernier = date('Ym', $moisDernier);


  //On prend la  liste des visiteurs médicaux qui ont des fiches a valider.
  $visiteurs = obtenirVisiteurQuiOntDesFiches($idConnexion, $moisDernier);
  
      ?>
      <div id=contenu>
        <h2> Validation des fiches de frais </h2>
          <?php
            if(!empty($visiteurs)){
            ?>
             <table class ="listeLegere">
                <tr>
                  <th class="qteForfait">Nom</th>
                  <th class="qteForfait">Prenom</th>
                  <th class="qteForfait">Fiche de frais</th>
                  <th class="qteForfait">Etat</th>
                </tr>
          
              <?php foreach($visiteurs as $visiteur) {?>
			 
               <tr>
                    <td><?php echo $visiteur['nom']?></td>
                    <td><?php echo $visiteur['prenom']?></td>
                    <td><a href="cAffichageFicheFrais.php?id=<?php echo $visiteur['id']?>"> lien vers la fiche </a></td>
                    <td><?php echo obtenirLibelleEtat($idConnexion, $visiteur['etat']);?></td>
               </tr>
              <?php
              }
          ?>
        </table>
      <?php
    }else{?>
      <p class=info> Aucune fiche a valider </p>
      <?php
    }
    ?>
    
  </div>
<?php
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?>
