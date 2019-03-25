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
 * Insérer la saisie du groupe de mots dans le formulaire de l’objet
 * 
 * @pipeline editer_contenu_objet
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_editer_contenu_objet($flux){

	$id_objet = $flux['args']['id'];
	$objet = $flux['args']['type'];
	$table_objet = table_objet($objet);

	// Recupérer les groupes de mots si la table correspondant à l'objet figure dans les tables liées de groupes de mots
	$groupes = sql_allfetsel('id_groupe, titre', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (count($groupes) > 0) {
		
		// Compatibilité avec le puglin Motus : si actif on regarde si il y a des restrictions et si oui, si elles s'appliquent 
		if (test_plugin_actif('motus')) {
			$trouver_table = charger_fonction('trouver_table', 'base');
			$desc = $trouver_table($table_objet); // ici peut être plutot utiliser declarer_parent ?

			// si l'objet à bien pour parent une rubrique
			if ($desc and isset($desc['field']['id_rubrique'])) {

				foreach ($groupes as $groupe) {
					$rubriques_ok = sql_getfetsel('rubriques_on', 'spip_groupes_mots', 'id_groupe='.intval($groupe['id_groupe']));
					$id_rubrique = $flux['args']['contexte']['id_parent'];

					if (motsdf_autoriser_groupe_si_selection_rubrique($rubriques_ok, 'rubrique', $id_rubrique, session_get('id_auteur'))) {
						$motdf =  array('nom_groupe' => $groupe['titre'], 'id_groupe' => $groupe['id_groupe'], 'id_objet' => $id_objet, 'objet' => $objet);
						$saisie_mot = recuperer_fond('inclure/inc-mots_cles', array_merge($flux['args']['contexte'], array('motdf'=>$motdf)));

						$flux['data'] = str_replace('<!--extra-->', '<!--extra-->' . $saisie_mot, $flux['data']);
					}
				}
			}
		}
		// Plugin Motus pas actif : on ajoute le groupe de mots
		else {
			foreach ($groupes as $groupe) {
				// sinon, on ajoute la saisie du ou des groupes de mots clés
				$motdf =  array('nom_groupe' => $groupe['titre'], 'id_groupe' => $groupe['id_groupe'], 'id_objet' => $id_objet, 'objet' => $objet);
				$saisie_mot = recuperer_fond('inclure/inc-mots_cles', array_merge($flux['args']['contexte'], array('motdf'=>$motdf)));

				$flux['data'] = str_replace('<!--extra-->', '<!--extra-->' . $saisie_mot, $flux['data']);
			}
		}
	}
	return $flux;
}



/**
 * Charger les mots-clés
 * 
 * @pipeline formulaire_traiter
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_formulaire_charger($flux) {
	$form = $flux['args']['form'];

	if (strncmp($form, 'editer_', 7) !== 0) {
		return $flux;
	}

	$objet = substr($form, 7);
	$table_objet = table_objet($objet);
	$id_objet = $flux['args']['args'][0];

	$groupes = sql_allfetsel('id_groupe, titre, obligatoire', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (count($groupes) > 0) {
		if (is_numeric($id_objet)) { // c'est une modification. On va chercher les valeurs dans la BdD
			foreach ($groupes as $groupe) {
				$champ = slugify($groupe['titre']);
				$liste = sql_allfetsel('M.id_mot', 'spip_mots AS M JOIN spip_mots_liens AS L ON M.id_mot=L.id_mot', "M.id_groupe=".intval($groupe['id_groupe'])." AND L.objet='$objet' AND L.id_objet=".intval($id_objet));
				$flux['data'][$champ] = array_column($liste, 'id_mot');
			}
		} else { // c'est une création (id_objet = 'new/oui'). On récupère les valeurs si elles sont postées (retour de la fonction verifier() par ex.)
			foreach ($groupes as $groupe) {
				$champ = slugify($groupe['titre']);
				$flux['data'][$champ] = _request($champ);
			}
		}
	}
	return $flux;

}

/**
 * Vérifier si la saisie d'un mot-clé au moins est obligatoire
 * 
 * @pipeline formulaire_traiter
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_formulaire_verifier($flux) {
	$form = $flux['args']['form'];
	
	if (strncmp($form, 'editer_', 7) !== 0) {
		return $flux;
	}
	$objet = substr($form, 7);
	$table_objet = table_objet($objet);

	$groupes = sql_allfetsel('id_groupe, titre, obligatoire', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (count($groupes) > 0) {
		// Compatibilité avec le puglin Motus : si actif et si on n'est pas dans un contexte rubrique ou que l'on est pas autoriser à montrer ce groupe dans cette rubrique, on sort
		if (test_plugin_actif('motus')) {

			$trouver_table = charger_fonction('trouver_table', 'base');
			$desc = $trouver_table($table_objet);

			if ($desc and isset($desc['field']['id_rubrique'])) {
				// contaxte : si c'est une creation, l'id_rubrique est déjà dans le contexte
				if (is_numeric($flux['args']['args']['1'])) {
					$id_parent = intval($flux['args']['args']['1']);
				}
				// sinon, on retrouve l'id_rubrique à partir de l'id_objet
				else {
					$table_objet_sql = table_objet_sql($table_objet);
					$id_table_objet  = id_table_objet($table_objet);
					$id_parent = sql_getfetsel('id_rubrique', $table_objet_sql, "$id_table_objet=".intval($flux['args']['args']['0']));
				}
			}

			// On a les infos de contexte : on peut maintenant vérifier pour chaque groupe ce qu'il en est
			foreach ($groupes as $groupe) {
				$rubriques_ok = sql_getfetsel('rubriques_on', 'spip_groupes_mots', 'id_groupe='.intval($groupe['id_groupe']));
				if (motsdf_autoriser_groupe_si_selection_rubrique($rubriques_ok, 'rubrique', $id_parent, session_get('id_auteur')) 
					and $groupe['obligatoire'] == 'oui') {
					$champ = slugify($groupe['titre']);
					$categories = _request($champ);

					if (!$categories) {
						$flux['data'][$champ] = _T('motsdf:saisir_choix');
					}
				}
			}
		}

		else {
			foreach ($groupes as $groupe) {
				if ($groupe['obligatoire'] == 'oui') {
					$champ = slugify($groupe['titre']);
					$categories = _request($champ);
					if (!$categories) {
						$flux['data']['categories_forum'] = _T('motsdf:saisir_choix');
					}
				}
			}
		}
	}
	return $flux;
}


/**
 * Gérer Ajout et/ou Suppression des mots-clés dans le formulaire d'edition de l'objet
 * 
 * @pipeline post_edition
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_post_edition($flux) {

	if (isset($flux['args']['table']) and $flux['args']['action'] == 'modifier') {
		$table_objet = $flux['args']['table_objet'];
		$groupes = sql_allfetsel('id_groupe, titre', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

		if (count($groupes) > 0) {
			include_spip('action/editer_mot');
			include_spip('spip_bonux_fonctions');
			
			$id_objet = $flux['args']['id_objet'];
			$objet = $flux['args']['type'];

			// gérer le cas où une checkbox est décochée : violent, mais pas trouvé mieux
			sql_delete('spip_mots_liens', "id_objet=".sql_quote($id_objet)." AND objet=".sql_quote($objet));

			foreach ($groupes as $groupe) {
				$champ = slugify($groupe['titre']);
				$categories = _request($champ);

				if (is_array($categories) AND count($categories) > 0) {
					foreach ($categories as $value) {
						mot_associer($value, array($objet => $id_objet));
					}
				}
			}
		}
	}
	
	return $flux;
}

/**
 * Fonction interne : SURCHARGE depuis plugin Motus
 * Retourne vrai si une selection de rubrique s'applique à cet objet
 * 
 * Autrement dit, si l'objet appartient à une des rubriques données
 *  
 * @param string $restrictions
 *     Liste des restrictions issues d'une selection avec le selecteur generique (rubrique|3)
 * @param string $objet
 *     Objet sur lequel on teste l'appartenance a une des rubriques (article)
 * @param int $id_objet
 *     Identifiant de l'objet.
 * @param int $qui
 *     De qui teste t'on l'autorisation.
 * @return bool
**/
function motsdf_autoriser_groupe_si_selection_rubrique($restrictions, $objet, $id_objet, $qui) {
	// si restriction a une rubrique...
	include_spip('formulaires/selecteur/generique_fonctions');
	include_spip('inc/autoriser');
	if ($rubs = picker_selected($restrictions, 'rubrique')) {

		// trouver la rubrique de l'objet en question
		if ($objet != 'rubrique') {

			$trouver_table = charger_fonction('trouver_table', 'base');
			$desc = $trouver_table( table_objet($objet) );

			if ($desc and isset($desc['field']['id_rubrique'])) {
				$table = table_objet_sql($objet);
				$id_rub = sql_getfetsel('id_rubrique', $table, id_table_objet($table) . '=' . intval($id_objet));
			}
		} else {
			$id_rub = $id_objet;
		}
		$opt = array();
		$opt['rubriques_on'] = $rubs;
		// ici on sait dans quelle rubrique est notre objet ($id_rub)
		// et on connait la liste des rubriques acceptées ($opt['rubriques_on'])
		return autoriser('dansrubrique', 'groupemots', $id_rub, $qui, $opt);
	}
	return false;
}

