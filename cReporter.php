<?php
/** 
 Script de contrôle et d'affichage du cas d'utilisation "saisir fiche de frais"
 */
$repInclude = './include/';
 require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecteé

if(! estVisiteurConnecte()){
	header("Location: cSeConnecter.php");
}
 require($repInclude . "_entete.inc.html");
 require($repInclude . "_sommaire.inc.php");
  

// supprimer le nbre de justification

 $nbJustificatif= recuperationJustificatif($idConnexion, $mois, $idvisiteur);
 $nbJustificatif= $nbJustificatif-1;
 ajoutNbJustificatif($idConnexion, $mois, $idVisiteur, $nbJustificatif);
 ?>
 
 <!-- Division principale-->
 <div id= "contenu">
	<?php
			if (substr($, 0,8) == "REFUSE: "){ ?>
				<p class = "info"> La fiche ne peut pas être reportée </p>
				<?php 
				header ("refresh:2, url = ./cValidationFicheFrais.php");
			}else {
				$moisS= reporterMois($_GET['mois']);
				$existeFicheFrais = existeFicheFrais ($idConnexion, $moisS, $_GET['idVisiteur']);
				// si elle n'existe pas, on la crée avec les éléments frais forfaitisés à 0
						if (! existeFicheFrais){
								// creation de la nouvelle fiche
								ajouterFicheFrais($idConnexion, $moisS, $_GET['id']);
						}
						$nbJustificatif=recuperationJustificatif ($idConnexion, $moisS, $_GET['idVisiteur']);
						$nbJustificatif= $nbJustificatif+1;
						ajoutNbJustificatif($idConnexion, $moisS, $_GET['idVisiteur'], $nbJustificatif);
						modifierLigneReportHF($idConnexion, $moisS, $_GET['idVisiteur']);
						
						header ("refresh:2, url = ./cValidationFicheFrais.php");
						?>
						<p class= "info"> La fiche a été reportée </p>
						<?php
						}
			?>
 </div>	
<?php
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?>

 
 
 
 

 