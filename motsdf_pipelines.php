<?php
/**
 * Utilisations de pipelines par Mots dans formulaires
 *
 * @plugin     Mots dans formulaires
 * @copyright  2018
 * @author     Peetdu
 * @licence    GNU/GPL
 * @package    SPIP\Motsdf\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Ajouter la saisie des mots-clés dont le groupe a été configurer pour s'afficher sur tel objet
 *
 * 
 * @pipeline editer_contenu_objet
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_editer_contenu_objet($flux){

	$id_objet = $flux['args']['id'];
	$objet = $flux['args']['type'];
	$table_objet = table_objet($objet);

	// Regarder si la table correspondant à l'objet en cours figure dans les tables liées de groupes de mots
	// todo : aller chercher ces infos dans la future configuration du plugin ?
	$groupes = sql_allfetsel('id_groupe, titre, unseul, obligatoire', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (is_array($groupes) AND count($groupes) > 0) {
		foreach ($groupes as $groupe) {
			$saisie_mot = recuperer_fond('inclure/inc-mots_cles', array('nom_groupe' => $groupe['titre'], 'id_groupe' => $groupe['id_groupe'], 'unseul' => $groupe['unseul'], 'obligatoire' => $groupe['obligatoire'], 'id_objet' => $id_objet, 'objet' => $objet));
			$flux['data'] = str_replace('<!--extra-->', '<!--extra-->' . $saisie_mot, $flux['data']);
		}
	}
	return $flux;
}


/**
 * Gérer l'ajout ou la suppression des mots-clés depuis le formulaire d'edition de l'objet
 * 
 * @pipeline editer_contenu_objet
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_post_edition($flux) {

	$table_objet = $flux['args']['table_objet'];
	$groupes = sql_allfetsel('id_groupe, titre', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (is_array($groupes) AND count($groupes) > 0 AND $flux['args']['action'] == 'modifier') {
		include_spip('action/editer_mot');
		include_spip('spip_bonux_fonctions');

		$id_objet = $flux['args']['id_objet'];
		$type_objet = $flux['args']['type'];

		// gérer le cas où une checkbox est décochée : violent, mais pas trouvé mieux
		sql_delete('spip_mots_liens', "id_objet=".sql_quote($id_objet)." AND objet=".sql_quote($type_objet));

		foreach ($groupes as $groupe) {
			$champ = slugify($groupe['titre']);
			$categories = _request($champ);

			if (is_array($categories) AND count($categories) > 0) {
				foreach ($categories as $value) {
					mot_associer($value, array($type_objet => $id_objet));
				}
			}
		}
	}
	return $flux;
}
