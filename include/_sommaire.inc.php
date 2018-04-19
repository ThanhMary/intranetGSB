<?php
/*Connexion  suivant le type de l' utilisateur  */
?>
     <div id="menuGauche">
     <div id="infosUtil">
    <?php
      if (estVisiteurConnecte()) {
          $idUser = obtenirIdUserConnecte() ;
          $lgUser = obtenirDetailVisiteur($idConnexion, $idUser);
          $nom = $lgUser['nom'];
          $prenom = $lgUser['prenom'];
    ?>
        <h2>
    <?php
            echo $nom . " " . $prenom ;
    ?>
        </h2>
    <?php
        if ($_SESSION['typeUser'] == 'V'){
    ?>
          <h3>Visiteur médical</h3>
    <?php
        }
        else{
    ?>
          <h3>Comptable</h3>
    <?php
       }
     }
    ?>
      </div>
<?php
  if (estVisiteurConnecte() ) {
?>
        <ul id="menuList">
          <li class="smenu">
             <a href="cAccueil.php" title="Page d'accueil">Accueil</a>
          </li>
          <li class="smenu">
             <a href="cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
          </li>

          <?php 
          
          if ( $_SESSION['typeUser'] == 'V'){ ?>
            <li class="smenu">
               <a href="cSaisieFicheFrais.php" title="Saisie fiche de frais du mois courant">Saisie fiche de frais</a>
            </li>
            <li class="smenu">
               <a href="cConsultFichesFrais.php" title="Consultation de mes fiches de frais">Consulter fiches de frais</a>
            </li>
          <?php
          }

          if ( $_SESSION['typeUser'] == 'C') { ?>
		   <li class="smenu">
               <a href="cValidationFicheFrais.php" title="Validation des fiches frais du mois dernier">Valider des frais</a>
            </li>
		      <li class="smenu">
                <a href="cSuiviPaiement.php" title="Suivi des mises en paiement des fiches validées">Suivre des frais</a>
            </li>
			
         
        
          <?php
          } ?>
        </ul>
        <?php
          // affichage des éventuelles erreurs déjà détectées
          if ( nbErreurs($tabErreurs) > 0 ) {
              echo toStringErreurs($tabErreurs) ;
          }
  }
        ?>
    </div>
