<?php
    /**
    * Page d'accueil de l'application web AppliFrais
    * @package default
    * @todo  RAS
    */
    $repInclude = './include/';
    require($repInclude . "_init.inc.php");
    
    // page inaccessible si visiteur non connecté
    if ( ! estVisiteurConnecte() )
    {
        header("Location: cSeConnecter.php");
    }
    require($repInclude . "_entete.inc.html");
    require($repInclude . "_sommaire.inc.php");
    
    $unMoisSaisi=lireDonneePost("lstMois", "");
    ?>
    
    <div id=contenu>
      <h2>Suivi du paiement des fiches frais</h2>
      <form action="" method="post">
        <div class="corpsForm">
          <input type="hidden" name="etape" value="validerConsult" />
          <p>
            <label for="lstMois">Mois : </label>
			<?php $req = obtenirReqMoisSuivi();
                $idJeuRes = mysqli_query($idConnexion, $req);
				$lgMois = mysqli_fetch_assoc($idJeuRes);
				?>
            <select id="lstMois" name="lstMois" title="Sélectionnez le mois souhaité pour le suivi">
			<?php
              // on propose tous les mois pour lesquels le visiteur a une fiche de frais
              while ( is_array($lgMois) ) {
                    $mois = $lgMois['mois'];
                    $noMois = intval(substr($mois, 4, 2));
                    $annee = intval(substr($mois, 0, 4));
	        ?>    
            <option value="<?php echo $mois; ?>"<?php if ($unMoisSaisi == $mois) { ?> selected="selected"<?php } ?>><?php echo obtenirLibelleMois($noMois) . " " . $annee; ?></option>
            <?php
                    $lgMois = mysqli_fetch_assoc($idJeuRes);        
					  mysqli_free_result($idJeuRes);
                }
              
            ?>
            </select>
          </p>
        </div>
		<div class="piedForm">
          <p>
            <input id="ok" type="submit" value="Valider" size="20"
                   title="Demandez à consulter cette fiche de frais" />
            <input id="annuler" type="reset" value="Effacer" size="20" />
          </p>
        </div>
      </form>
        
      <?php
      if ($unMoisSaisi != ''){
        ?>
          
        <table class ="listeLegere">
					  <tr>
						<th class"qteForfait"> Nom </th>
						<th class"qteForfait"> Date </th>
						<th class"qteForfait"> Etat </th>
						<th class="action">&nbsp;</th>
					  </tr>
        
					  <?php
					  $fiches = obtenirFichesValide($idConnexion, $unMoisSaisi);

					  foreach($fiches as $fiche) {
			 
								$id = $fiche['id'];
								?>
											<tr>
											  <td><?php echo $fiche['nom']?> <?php echo $fiche['prenom'] ?></td>
											  <td><?php echo obtenirLibelleMois(intval(substr($fiche['mois'],4,2))).' '.substr($fiche['mois'],0,4)?></td>
											  <td><?php echo obtenirLibelleEtat($idConnexion, $fiche['etat']);?></td>
											  <td><a href='cAffichageFicheFrais.php?id=<?php echo $id ?>&amp;date=<?php echo $fiche['mois'] ?>&amp;etape=miseEnPaiement'> lien vers la fiche </a></td>
											</tr>
								<?php
						}
					?>
        </table>
       <?php
      }
      ?>
     
      
    </div>

<?php
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?>    
