<?php
/**
 * API de fonctions utilesdans le cas de l'utilisation de mot-sclés
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
 * @example
 *  [(#SAISIE{checkbox, id_mot, label=Choisir une case à cocher, datas=[(#VAL{4}|motsdf_liste_mots)]})]
 * 
 *
 * @param int id_groupe
 *		Clé des arguments. En absence utilise l'argument
 *
 * @return array
 *		array(id_mot -> titre, etc.)
 */
function motsdf_liste_mots($id_groupe, $defaut = null) {
	$liste_mots = array();
	
	if (!is_null($defaut)) {
		$liste_mots = array('' => $defaut);
	}
	$res = sql_allfetsel('id_mot, titre', 'spip_mots', 'id_groupe='.intval($id_groupe));
	foreach ($res as $value) {
		$add_mots[$value['id_mot']] = $value['titre'];

	}
	$liste_mots = $liste_mots + $add_mots;
	return $liste_mots;
}

/**
 * Traiter un lot de demandes de liaisons et/ou de dissociations
 * Bien utile pour les traitements par lot, par exemple un tableau de cases à cocher dans un formulaire
 *
 * @example
 *     ```
 *		count(_request('secteurs_activite')) > 0 ? $liste_mots_coches = _request('secteurs_activite') : $liste_mots_coches = array();
		$liste_mots_coches = array_filter($liste_mots_coches);
 *      objet_correspondance_association('article', $id_article, 'mot', $liste_mots_coches);
 *     ```
 *
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
