<?php
/**
 * Fonctions utiles dans le cas de l'utilisation de mots-clés
 * + deux fonctions API
 *
 * @plugin     Mots dans formulaires
 * @copyright  2018
 * @author     Peetdu
 * @licence    GNU/GPL
 * @package    SPIP\Motsdf\API
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourner le tableau id_mot/titre d'un groupe de mots clés
 * Utile pour les #SAISIE -> datas
 *
 * @example
 *  [(#SAISIE{checkbox, id_mot, Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]
 *  [(#SAISIE{radio, categorie, datas=[(#VAL{1}|motsdf_liste_mots{valeur_par_defaut})]})]
 * 
 * @api
 * @param int id_groupe
 *		ID du groupe de mots dont on veut recupérer la liste.
 * @param string defaut
 *		Valeur par defaut du tableau de mots
 *
 * @return array
 *		array(id_mot -> titre, etc.)
 */
function motsdf_liste_mots($id_groupe, $defaut = null) {
	$liste_mots = array();
	
	if ($defaut) {
		$liste_mots = array('' => $defaut);
	}

	$res = sql_allfetsel('id_mot, titre', 'spip_mots', 'id_groupe='.intval($id_groupe));
	foreach ($res as $value) {
		$add_mots[$value['id_mot']] = $value['titre'];
	}

	if (isset($add_mots)){
		$liste_mots += $add_mots;
	}

	return $liste_mots;
}

/**
 * Traiter un lot de demandes de liaisons et/ou de dissociations
 * Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire
 *
 * @example
 *     '''
 *		count(_request('secteurs_activite')) > 0 ? $liste_mots_coches = _request('secteurs_activite') : $liste_mots_coches = array();
 *		$liste_mots_coches = array_filter($liste_mots_coches);
 *      objet_correspondance_association('article', $id_article, 'mot', $liste_mots_coches);
 *		'''
 *
 * @api
 * @param string $objet
 *     type de l'objet
 * @param int $id_objet
 *     id de l'objet
 * @param string $type_objet_liens
 *     préciser le type objet de la table de liaison voulue (ex. : 'mot')
 * @param array $liaisons_demandees
 *     le tableau contenant les nouvelles liaisons demandées
 */
function objet_correspondance_association($objet, $id_objet, $type_objet_liens, $liaisons_demandees) {

	include_spip('action/editer_liens');
	
	/* récupérer les associations existantes avant modif */
	// la table de liaison
	$liaison = objet_associable($type_objet_liens);
	$id_objet_lien = $liaison[0];
	$table_lien = $liaison[1];

	// aller chercher les liaisons existantes en base de données
	$ids_liens = sql_allfetsel("$id_objet_lien", "$table_lien", 'objet='.sql_quote($objet).' AND id_objet='.intval($id_objet));
	$ids_liens = array_column($ids_liens, $id_objet_lien);

	/* Préparer les tableaux de valeurs */
	// le tableau des associations
	$associer = array_diff($liaisons_demandees, $ids_liens);

	// le tableau des dissociations
	$tjs_la = array_intersect($liaisons_demandees, $ids_liens);
	$dissocier = array_diff($ids_liens, $tjs_la);

	/* Traiter */
	objet_associer(array($type_objet_liens => $associer), array($objet => $id_objet));
	objet_dissocier(array($type_objet_liens => $dissocier), array($objet => $id_objet));

}

/**
 * Tester si des groupes de mots-clés ont été activés pour le formulaire de cet objet
 * 
 * @param string $objet
 * @param bool $force
 * @return array|bool renvoi un tableau avec les groupes activés pour cet objet, false sinon
**/ 
function motsdf_groupes_actifs_objet($objet, $force = true) {
	if ($force) {
		$objets = table_objet($objet); // la version plurielle du type de l'objet
	}
	else {
		$objets = $objet; 
	}
	
	$select = array('id_groupe', 'titre', 'obligatoire');
	
	// Compatibilité avec le plugin Mots Arborescents
	if (test_plugin_actif('motsar')) {
		$select[] = 'mots_arborescents';
	}

	// Regarder si l'objet a été sélectionné dans la configuration du groupe de mots-clés
	$activation = sql_allfetsel($select, 'spip_groupes_mots', "tables_liees LIKE '%$objets%'");

	// Donner aux plugins la possibilité d'ajouter ou d'enlever un groupe de mots pour un objet en particulier
	$activation = pipeline('motsdf_activer_objet', $activation);


	if (!empty($activation)) {
		return $activation;
	}

	return false;
}

/**
 * Trouver l'id_rubrique de l'objet dans l’environnement (cas d'une création) sinon dans la base de données
 * Marche aussi si l'info sur le parent est contenu dans une table de liaison (spip_blocks_lien par exemple).
 * Fonction utile uniquement pour la compatibilité avec le plugin Motus
 * 
 * @param string $objet
 * @param int $id_objet
 * @return int|bool renvoi l'id_rubrique, false sinon
**/ 
function motsdf_trouver_id_parent($objet, $id_objet) {

	/* Création de l'objet : récupérer l'id_rubrique dans le contexte */
	$id_rubrique = _request('id_rubrique');
	// $association = _request('associer_objet');

	/* Modification : aller chercher l'id_rubrique directement dans la table ou dans une liaison */
	if (
		!$id_rubrique
		and is_numeric($id_objet)
	) {
		$table_objet_sql = table_objet_sql($objet);
		$trouver_table = charger_fonction('trouver_table', 'base');
		$desc = $trouver_table($table_objet_sql);
		if (
			$desc 
			and isset($desc['field']['id_rubrique'])
		) {
			switch ($objet) {
				case 'rubrique':
					$id_rubrique = $id_objet; // pour une rubrique on renvoie l'id_rubrique lui même, pas son parent
					break;
				default:
					$id_table_objet = id_table_objet($objet);
					$id_rubrique = sql_getfetsel('id_rubrique', $table_objet_sql, "$id_table_objet=".$id_objet);
					break;
			}
		} else { // Sinon, regarder si l'objet a une table de liaison
			include_spip('action/editer_liens');
			if (objet_associable($objet)) {
				 $liens = objet_trouver_liens(array($objet => $id_objet), '*');
				 if (isset($liens['rubrique'])) {
				 	$id_rubrique = $liens['id_objet'];
				 }
			}
		}
	}
	
	return $id_rubrique;
}
