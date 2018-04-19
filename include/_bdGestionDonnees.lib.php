<?php
/** 
 * Regroupe les fonctions d'acc�s aux donn�es.
 * @package default
 * @author Arthur Martin
 * @todo Fonctions retournant plusieurs lignes sont � r��crire.
 */

/** 
 * Se connecte au serveur de donn�es MySql.                      
 * Se connecte au serveur de donn�es MySql � partir de valeurs
 * pr�d�finies de connexion (h�te, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succ�s obtenu, le bool�en false 
 * si probl�me de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "root";
    $mdp = "";
	$bd = "gsb_frais";
    return $idCnx = mysqli_connect($hote, $login, $mdp, $bd);
}

/**
 * S�lectionne (rend active) la base de donn�es.
 * S�lectionne (rend active) la BD pr�d�finie gsb_frais sur la connexion
 * identifi�e par $idCnx. Retourne true si succ�s, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succ�s ou �chec de s�lection BD 
 */
function activerBD($idCnx) {
    $bd = "gsb_frais";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caract�res de la connexion
    $res = mysqli_query($idCnx, $query); 
    $ok = mysqli_select_db($idCnx, $bd);
    return $ok;
}

/** 
 * Ferme la connexion au serveur de donn�es.
 * Ferme la connexion au serveur de donn�es identifi�e par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysqli_close($idCnx);
}

/**
 * Echappe les caract�res sp�ciaux d'une cha�ne.
 * Envoie la cha�ne $str �chapp�e, c�d avec les caract�res consid�r�s sp�ciaux
 * par MySql (tq la quote simple) pr�c�d�s d'un \, ce qui annule leur effet sp�cial
 * @param string $str cha�ne � �chapper
 * @return string cha�ne �chapp�e 
 */    
function filtrerChainePourBD($idCnx, $str) {
    if ( ! get_magic_quotes_gpc() ) { 
        // si la directive de configuration magic_quotes_gpc est activ�e dans php.ini,
        // toute cha�ne re�ue par get, post ou cookie est d�j� �chapp�e 
        // par cons�quent, il ne faut pas �chapper la cha�ne une seconde fois                              
        $str = mysqli_real_escape_string($idCnx, $str);
    }
    return $str;
}

/** 
 * Fournit les informations sur un Utilisateur demand�. 
 * Retourne les informations du Utilisateur d'id $unId sous la forme d'un tableau
 * associatif dont les cl�s sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif du Utilisateur
 */
function obtenirDetailVisiteur($idCnx, $unId) {
    $id = filtrerChainePourBD($idCnx, $unId);
    $requete = "SELECT id, nom, prenom 
						  FROM utilisateur 
						  WHERE id='" . $unId . "'";
						  
    $idJeuRes = mysqli_query($idCnx, $requete);  
    $ligne = false;     
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
        mysqli_free_result($idJeuRes);
    }
    return $ligne ;
}


/** 
 * Fournit les informations d'une fiche de frais. 
 * Retourne les informations de la fiche de frais du mois de $unMois (MMAAAA)
 * sous la forme d'un tableau associatif dont les cl�s sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id Utilisateur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    $ligne = false;
    $requete="select IFNULL(nbJustificatifs,0) as nbJustificatifs, Etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide 
    from FicheFrais inner join Etat on idEtat = Etat.id 
    where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    $idJeuRes = mysqli_query($idCnx, $requete);  
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
    }        
    mysqli_free_result($idJeuRes);
    
    return $ligne ;
}
              
/** 
 * V�rifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du Utilisateur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id Utilisateur  
 * @return bool�en existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    $requete = "select idVisiteur from FicheFrais where idVisiteur='" . $unIdVisiteur . 
              "' and mois='" . $unMois . "'";
    $idJeuRes = mysqli_query($idCnx, $requete);  
    $ligne = false ;
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
        mysqli_free_result($idJeuRes);
    }        
    
    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne) ;
}
function reporterMois($unMois) {
	$unMoisReport=0;

	if (substr($unMois, 4, 2)<12)
	{
		$unMoisReport=$unMois+1;
	}
	else
	{
		$unMoisReport=($unMois+100)-11;
	}
	
	return $unMoisReport; 
}

function ajoutNbJustificatif($idCnx, $unMois, $unIdVisiteur,$nbJustificatif){
	$requete = "update FicheFrais set nbJustificatifs ='".$nbJustificatif."' where idVisiteur = '".$unIdVisiteur."' and mois = '".$unMois."'";
	mysqli_query($idCnx, $requete);
}
function recuperationJustificatif($idCnx, $unMois, $unIdVisiteur){
	$requete = "select count(id) from lignefraishorsforfait where idVisiteur = '".$unIdVisiteur."' and mois = '".$unMois."'";
	$res = mysqli_query($idCnx, $requete) or die (mysql_error()); 
	$resultat=mysqli_fetch_row($res); 
	return $resultat[0];
}

function modifierLigneReportHF($idCnx, $unMois,$id) {
    $requete = "update LigneFraisHorsForfait set mois ='".$unMois."' where id='".$id."'";
                
    mysqli_query($idCnx, $requete);
	
}

/** 
 * Fournit le mois de la derni�re fiche de frais d'un Utilisateur.
 * Retourne le mois de la derni�re fiche de frais du Utilisateur d'id $unIdVisiteur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id Utilisateur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdVisiteur) {
	$requete = "select max(mois) as dernierMois from FicheFrais where idVisiteur='" .
            $unIdVisiteur . "'";
	$idJeuRes = mysqli_query($idCnx, $requete);
    $dernierMois = false ;
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
        $dernierMois = $ligne["dernierMois"];
        mysqli_free_result($idJeuRes);
    }        
	return $dernierMois;
}

/** 
 * Ajoute une nouvelle fiche de frais et les �l�ments forfaitis�s associ�s, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du Utilisateur 
 * $idVisiteur, avec les �l�ments forfaitis�s associ�s dont la quantit� initiale
 * est affect�e � 0. Cl�t �ventuellement la fiche de frais pr�c�dente du Utilisateur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id Utilisateur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    // modification de la derni�re fiche de frais du Utilisateur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
	$laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
	if ( is_array($laDerniereFiche) && $laDerniereFiche['idEtat']=='CR'){
		modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
	}
    
    // ajout de la fiche de frais � l'�tat Cr��
    $requete = "insert into FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('" 
              . $unIdVisiteur 
              . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysqli_query($idCnx, $requete);
    
    // ajout des �l�ments forfaitis�s
    $requete = "select id from FraisForfait";
    $idJeuRes = mysqli_query($idCnx, $requete);
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
        while ( is_array($ligne) ) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                        values ('" . $unIdVisiteur . "','" . $unMois . "','" . $idFraisForfait . "',0)";
            mysqli_query($idCnx, $requete);
            // passage au frais forfait suivant
            $ligne = mysqli_fetch_assoc ($idJeuRes);
        }
        mysqli_free_result($idJeuRes);       
    }        
}

/**
 * Retourne le texte de la requ�te select concernant les mois pour lesquels un 
 * Utilisateur a une fiche de frais. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le Utilisateur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id Utilisateur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqMoisFicheFrais($unIdVisiteur) {
	    $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idVisiteur ='"
            . $unIdVisiteur . "' order by fichefrais.mois desc ";
    return $req ;
}  
                  
/**
 * Retourne le texte de la requ�te select concernant les �l�ments forfaitis�s 
 * d'un Utilisateur pour un mois donn�s. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir l'id, le libell� et la
 * quantit� des �l�ments forfaitis�s de la fiche de frais du Utilisateur
 * d'id $idVisiteur pour le mois $unMois    
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id Utilisateur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqEltsForfaitFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
			  

    return $requete;
}

/**
 * Retourne le texte de la requ�te select concernant les �l�ments hors forfait 
 * d'un Utilisateur pour un mois donn�s. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir l'id, la date, le libell� 
 * et le montant des �l�ments hors forfait de la fiche de frais du Utilisateur
 * d'id $idVisiteur pour le mois $unMois    
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id Utilisateur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqEltsHorsForfaitFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait
              where idVisiteur='" . $unIdVisiteur 
              . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Supprime une ligne hors forfait.
 * Supprime dans la BD la ligne hors forfait d'id $unIdLigneHF
 * @param resource $idCnx identifiant de connexion
 * @param string $idLigneHF id de la ligne hors forfait
 * @return void
 */
function supprimerLigneHF($idCnx, $unIdLigneHF) {
    $requete = "delete from LigneFraisHorsForfait where id = " . $unIdLigneHF;
    mysqli_query($idCnx, $requete);
}

/**
 * Ajoute une nouvelle ligne hors forfait.
 * Ins�re dans la BD la ligne hors forfait de libell� $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu � la date $uneDateHF pour la fiche de frais du mois
 * $unMois du Utilisateur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (AAMMMM)
 * @param string $unIdVisiteur id du Utilisateur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libell� du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdVisiteur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $unLibelleHF = filtrerChainePourBD($idCnx, $unLibelleHF);
    $uneDateHF = filtrerChainePourBD($idCnx, convertirDateFrancaisVersAnglais($uneDateHF));
    $unMois = filtrerChainePourBD($idCnx, $unMois);
    $requete = "insert into LigneFraisHorsForfait(idVisiteur, mois, date, libelle, montant) 
                values ('" . $unIdVisiteur . "','" . $unMois . "','" . $uneDateHF . "','" . $unLibelleHF . "'," . $unMontantHF .")";
    mysqli_query($idCnx, $requete);
}

/**
 * Modifie les quantit�s des �l�ments forfaitis�s d'une fiche de frais. 
 * Met � jour les �l�ments forfaitis�s contenus  
 * dans $desEltsForfaits pour le Utilisateur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, apr�s avoir filtr� 
 * (annul� l'effet de certains caract�res consid�r�s comme sp�ciaux par 
 *  MySql) chaque donn�e   
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA) 
 * @param string $unIdVisiteur  id Utilisateur
 * @param array $desEltsForfait tableau des quantit�s des �l�ments hors forfait
 * avec pour cl�s les identifiants des frais forfaitis�s 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdVisiteur, $desEltsForfait) {
    $unMois=filtrerChainePourBD($idCnx, $unMois);
    $unIdVisiteur=filtrerChainePourBD($idCnx, $unIdVisiteur);
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update LigneFraisForfait set quantite = " . $quantite 
                    . " where idVisiteur = '" . $unIdVisiteur . "' and mois = '"
                    . $unMois . "' and idFraisForfait='" . $idFraisForfait . "'";
      mysqli_query($idCnx, $requete);
    }
}

/**
 * Contr�le les informations de connexionn d'un utilisateur.
 * V�rifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les cl�s sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le bool�en false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou bool�en false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($idCnx, $unLogin);
    $unMdp = filtrerChainePourBD($idCnx, $unMdp);
    // le mot de passe est crypt� dans la base avec la fonction de hachage md5
    $req = "SELECT id, nom, prenom, login, mdp, type 
				  FROM utilisateur 
				  WHERE login='".$unLogin."' and mdp='" . $unMdp . "'";
				  
    $idJeuRes = mysqli_query($idCnx, $req);
    $ligne = false;
    if ( $idJeuRes ) {
        $ligne = mysqli_fetch_assoc($idJeuRes);
        mysqli_free_result($idJeuRes);
    }
	
    return $ligne;
}

/**
 * Modifie l'�tat et la date de modification d'une fiche de frais
 
 * Met � jour l'�tat de la fiche de frais du Utilisateur $unIdVisiteur pour
 * le mois $unMois � la nouvelle valeur $unEtat et passe la date de modif � 
 * la date d'aujourd'hui
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @return void 
 */
function modifierEtatFicheFrais($idCnx, $unMois, $unIdVisiteur, $unEtat) {
    $requete = "update FicheFrais set idEtat = '" . $unEtat . 
               "', dateModif = now() where idVisiteur ='" .
               $unIdVisiteur . "' and mois = '". $unMois . "'";
    mysqli_query($idCnx, $requete);
}             

/** des fonctions qui r�pondent  aux travaux de comptable
*
*
*/
function obtenirVisiteurQuiOntDesFiches($Cnx, $mois){
    $requete= " SELECT id, nom, prenom, idEtat
							FROM utilisateur
							INNER JOIN FicheFrais ON utilisateur.id = FicheFrais.idVisiteur
							WHERE mois LIKE '$mois' AND  type= 'V'  AND idEtat = 'CL' 
							GROUP BY nom ";
							
	$visiteurs = mysqli_query ($Cnx, $requete);
	
	while ($visiteur = mysqli_fetch_assoc($visiteurs) ){
		$tabvisiteurs [] = array (
			'id'				=> $visiteur['id'],
			'nom'			=> $visiteur['nom'],
			'prenom'	=> $visiteur['prenom'],
			'etat'			=> $visiteur['idEtat']
		);
		return $tabvisiteurs;		
	}
}
function obtenirReqMoisSuivi(){
    $requete = "select distinct idVisiteur, mois, montantValide, nom, prenom 
			FROM FicheFrais
            INNER JOIN utilisateur ON FicheFrais.idVisiteur = utilisateur.id
            WHERE type = 'V' and idEtat = 'VA' ";
				  
    return $requete;
}
function obtenirReqFicheFraisCloture(){
	$requete = "select distinct idVisiteur, mois, montantValide, nom, prenom 
			FROM FicheFrais
            INNER JOIN utilisateur ON FicheFrais.idVisiteur = utilisateur.id
            WHERE type = 'V' and idEtat = 'CL'  "; 
    return $requete;
}

function obtenirFicheFraisForfaits($Cnx, $id,$unMois){
    $requete = "SELECT idFraisForfait, libelle, quantite, montant, idEtat
                FROM LigneFraisForfait
                INNER JOIN FicheFrais ON LigneFraisForfait.idVisiteur = FicheFrais.idVisiteur
                INNER JOIN FraisForfait ON LigneFraisForfait.idFraisForfait = FraisForfait.id
                WHERE FicheFrais.idVisiteur LIKE '$id'
                    AND FicheFrais.mois LIKE '$unMois'
                  GROUP BY libelle " ;

    $infoFicheForfait = mysqli_query($Cnx, $requete);
  
    while ($fiche = mysqli_fetch_assoc($infoFicheForfait)){

      $tabFiches[] = array(
          'libelle' 		 	 => $fiche['libelle'],
          'quantite' 	 => $fiche['quantite'],
          'etat'    			 => $fiche['idEtat'],
          'frais' 		   	 => $fiche['idFraisForfait'],
          'montant'   	 => $fiche['montant']
      );
	  return $tabFiches;
    }
	
}

function obtenirFicheFraisHorsForfait($Cnx, $id, $unMois){
  $requete = "SELECT libelle, date, montant
              FROM LigneFraisHorsForfait
              WHERE idVisiteur LIKE '$id'
                  AND mois LIKE '$unMois'";
   
  $infoFicheHorsForfait = mysqli_query($Cnx, $requete);

	  while( $fiche = mysqli_fetch_assoc($infoFicheHorsForfait)){

		$tabFiches[] = array(
			'libelle' => $fiche['libelle'],
			'date'    => $fiche['date'],
			'montant' => $fiche['montant']
			);
	   }
  mysqli_free_result($infoFicheHorsForfait);
  if(isset($tabFiches))
		return $tabFiches;
  else
		return  false;
}

function obtenirFraisForfait($Cnx, $idForfait){
  $requete = "SELECT montant
              FROM FraisForfait
              WHERE id LIKE '$idForfait'";

  $resultatReq = mysqli_query($Cnx, $requete);

  $resultat = mysqli_fetch_assoc($resultatReq);
  mysqli_free_result($resultatReq);
  return floatval($resultat['montant']);
}

function obtenirLibelleEtat($Cnx, $idEtat){
    $requete = "SELECT libelle
                FROM Etat
                WHERE id LIKE '$idEtat' " ;
    
    $libelle = mysqli_query($Cnx, $requete);
    $libelle = mysqli_fetch_assoc($libelle);
    return $libelle['libelle'];
}

function cloturerFicheFrais($Cnx){
    $date = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
    $unMois =  date('Ym', $date);
    
    $req = "UPDATE FicheFrais
            SET idEtat = 'CL'
            WHERE mois = '$unMois'
            AND idEtat = 'CR'";
            
    mysqli_query($Cnx, $req);
}

function validerFicheFrais($Cnx, $id){
    $date = mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"));
    $unMois =  date('Ym', $date);
    
    $requete = "UPDATE FicheFrais 
                SET idEtat = 'VA' 
                WHERE idVisiteur = '$id' AND mois = '$unMois'";
                
    mysqli_query($Cnx, $requete);
}

function mettreEnPaiementFicheFrais($Cnx, $id, $date){
   
    $requete = "UPDATE FicheFrais 
                SET idEtat = 'MP' 
                WHERE idVisiteur = '$id' AND mois = '$date' ";
            
    mysqli_query($Cnx, $requete);
}

function obtenirFichesValide($Cnx, $unMois){
    $req = " SELECT id, nom, prenom, mois, idEtat
            FROM FicheFrais
            INNER JOIN utilisateur ON FicheFrais.idVisiteur = utilisateur.id
            WHERE idEtat = 'VA' AND mois='$unMois'
             OR idEtat = 'MP' AND mois='$unMois'
             ORDER BY nom ";
    
    $fiches = mysqli_query($Cnx, $req);
    
     while( $fiche = mysqli_fetch_assoc($fiches)){
						$tabFiche[] = array(
							'id'       => $fiche['id'],
							'nom'      => $fiche['nom'],
							'prenom'   => $fiche['prenom'],
							'mois'     => $fiche['mois'],
							'etat'     => $fiche['idEtat'] 
						);
  }
    mysqli_free_result($fiches);
     return $tabFiche;
}

function refuserLigneHF ($Cnx, $unIdLigneHF, $unIdLibelleLigneHF){
	$requete = "UPDATE ligneFraisHorsForfait
							SET libelle = 'REFUSE-$unIdLibelleLigneHF'
							WHERE id = '$unIdLibelleLigneHF' ";
	mysqli_query($Cnx, $requete);
}

function validerLigneHF($Cnx, $unIdLigneHF, $unLibelleLigneHF){
	// calculer la longueur de la chaine sans  "REFUSE"
	$longueur=strlen($unLibelleLigneHF)-7;
	// r�cuperer la chaine apres "REFUSE-" jusqu'� la fin
	$unLibelleLigneHF= substr ($unLibelleLigneHF, 7 , $longueur);
	
	$requete= "UPDATE LigneFraisHorsForfait
							SET libelle = '$unLibelleLigneHF'
							WHERE id = '$unIdLigneHF' ";
	mysqli_query ($Cnx, $requete);
}

?>

