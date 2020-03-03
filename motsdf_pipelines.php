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
 * Insérer la saisie du ou des groupes de mots dans le formulaire de l’objet
 * 
 * @pipeline editer_contenu_objet
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_editer_contenu_objet($flux) {
	$objet = $flux['args']['type'];

	if ($groupes = motsdf_groupes_actifs_objet($objet)) {
		$id_objet = $flux['args']['id'];
		
		// Compatibilité plugin Motus
		// Si actif et si présence d'un id_rubrique dans le contexte, on regarde si des restrictions s'appliquent.
		$id_rubrique = motsdf_trouver_id_parent($objet, $id_objet); 

		if (
			test_plugin_actif('motus')
			and $id_rubrique
		) {
			foreach ($groupes as $groupe) {
				$restrictions = sql_getfetsel('rubriques_on', 'spip_groupes_mots', 'id_groupe='.intval($groupe['id_groupe']));
				$rubs = picker_selected($restrictions, 'rubrique');

				// Dans l'idéal, il faudrait faire ça : autoriser('dansrubrique', 'groupemots', $id_rubrique, session_get('id_auteur'), $opt)
				// Mais il faudrait alors pourvoir aussi autoriser les visiteurs à voir les mots-clés

				if (!$restrictions or in_array($id_rubrique, $rubs)) {
					$motdf =  array('nom_groupe' => $groupe['titre'], 'id_groupe' => $groupe['id_groupe'], 'id_objet' => $id_objet, 'objet' => $objet);
					$inclure = (isset($groupe['mots_arborescents']) && $groupe['mots_arborescents'] == 'oui' ? 'inclure/inc-mots_cles_arbo_df' : 'inclure/inc-mots_cles_df');
					$saisie_mot = recuperer_fond($inclure, array_merge($flux['args']['contexte'], array('motdf'=>$motdf))
				);

					$flux['data'] = str_replace('<!--extra-->', '<!--extra-->' . $saisie_mot, $flux['data']);
				}
			}
		} else { // Plugin Motus pas actif ou pas de id_rubrique dans le contexte : on ajoute le groupe de mots
			foreach ($groupes as $groupe) {
				// ajouter la saisie du ou des groupes de mots clés
				$motdf =  array('nom_groupe' => $groupe['titre'], 'id_groupe' => $groupe['id_groupe'], 'id_objet' => $id_objet, 'objet' => $objet);
				$inclure = (isset($groupe['mots_arborescents']) && $groupe['mots_arborescents'] == 'oui' ? 'inclure/inc-mots_cles_arbo_df' : 'inclure/inc-mots_cles_df');
				$saisie_mot = recuperer_fond($inclure, array_merge($flux['args']['contexte'], array('motdf'=>$motdf)));

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
	if ($groupes = motsdf_groupes_actifs_objet($objet)) {

		$id_objet = $flux['args']['args'][0];
		$id_rub = motsdf_trouver_id_parent($objet, $id_objet);
		

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
	if ($groupes = motsdf_groupes_actifs_objet($objet)) {

		$table_objet = table_objet($objet); // ex. : articles
		$table_objet_sql = table_objet_sql($table_objet); // ex. : spip_articles
		$id_table_objet  = id_table_objet($table_objet); // ex. : id_article
		$id_objet = _request($id_table_objet);
		
		// Compatibilité avec le plugin Motus : si actif on regarde si on est bien avec dans un contexte de rubrique + si il y a des restrictions. Et si oui, si elles s'appliquent.
		$id_rub = motsdf_trouver_id_parent($objet, $id_objet); 
		if (
			test_plugin_actif('motus')
			and $id_rub
		) {

			$trouver_table = charger_fonction('trouver_table', 'base');
			$desc = $trouver_table($table_objet);

			if ($desc and isset($desc['field']['id_rubrique'])) {
				// contexte : si c'est une creation, l'id_rubrique est déjà dans le contexte
				if (is_numeric($flux['args']['args']['1'])) {
					$id_parent = intval($flux['args']['args']['1']);
				}
				// sinon, on retrouve l'id_rubrique à partir de l'id_objet
				else {
					$id_parent = sql_getfetsel('id_rubrique', $table_objet_sql, "$id_table_objet=".intval($flux['args']['args']['0']));
				}
			} else {
				// vérifier si ce n'est pas une liaison à une rubrique via une table de liens
				include_spip('action/editer_liens');
				if (objet_associable($objet)) {
					 $liens = objet_trouver_liens(array($objet => $id_objet), '*');
					 if (isset($liens['rubrique'])) {
					 	$id_parent = $liens['rubrique'];
					 }
				}
			}

			// Si on a récupéré un id_parent  (id_rubrique en fait), on vérifie si il fait parti des restrictions défini dans la config
			if (isset($id_parent)) {
				foreach ($groupes as $groupe) {
					$restrictions = sql_getfetsel('rubriques_on', 'spip_groupes_mots', 'id_groupe='.intval($groupe['id_groupe']));
					$rubs = picker_selected($restrictions, 'rubrique');
					if (!$restrictions or in_array($id_parent, $rubs) 
						and $groupe['obligatoire'] == 'oui') {
						$champ = slugify($groupe['titre']);
						$categories = _request($champ);

						if (!$categories) {
							$flux['data'][$champ] = _T('motsdf:saisir_choix');
						}
					}
				}
			}
			
		}

		// si le plugin Motus n'est pas présent, on test juste si 
		else {
			foreach ($groupes as $groupe) {
				if ($groupe['obligatoire'] == 'oui') {
					$champ = slugify($groupe['titre']);
					$categories = _request($champ);
					if (!$categories) {
						$flux['data'][$champ] = _T('motsdf:saisir_choix');
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
 * @pipeline formulaire_traiter
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 

/*
Notice: Undefined index: id_composition_objet in /Users/akilia/htdocs/meric/plugins/motsdf/motsdf_pipelines.php on line 218
*/
function motsdf_formulaire_traiter($flux) {
	$form = $flux['args']['form'];
	
	if (strncmp($form, 'editer_', 7) !== 0) {
		return $flux;
	}

	$objet = substr($form, 7);
	$table_objet = table_objet($objet);
	$groupes = sql_allfetsel('id_groupe, titre', 'spip_groupes_mots', "tables_liees LIKE '%$table_objet%'");

	if (count($groupes) > 0) {
		include_spip('action/editer_mot');
		include_spip('spip_bonux_fonctions');
		$id_table_objet = id_table_objet($objet);
		$id_objet = $flux['data'][$id_table_objet];

		// gérer le cas où une checkbox est décochée : violent, mais pas trouvé mieux
		sql_delete('spip_mots_liens', "id_objet=".sql_quote($id_objet)." AND objet=".sql_quote($objet));

		foreach ($groupes as $groupe) {
			$champ = slugify($groupe['titre']);
			$categories = _request($champ);

			if (is_array($categories) AND count($categories) > 0) {
				foreach ($categories as $value) {
					$assoc = mot_associer($value, array($objet => $id_objet));
				}
			}
		}
	}
	
	return $flux;
}

/**
 * Afficher les groupes de mots liés à un objet dans la page exec=objets
 * 
 * 
 * @pipeline affiche_droite
 * @param array $flux Données du pipeline
 * @return array      Données du pipeline
**/ 
function motsdf_affiche_droite($flux) {
	$objets = $flux['args']['exec'];
	if ($groupes = motsdf_groupes_actifs_objet($objets, false)) {
		foreach ($groupes as $groupe) {
			$inclure = "prive/squelettes/inclure/groupe_mots_$objets";
			if(find_in_path($inclure)){
				$id_groupe = $groupe['id_groupe'];
				$titre_groupe = $groupe['titre'];
				$flux['data'] .= recuperer_fond($inclure, array('id_groupe' => $id_groupe, 'titre' => $titre_groupe));
			}
		}
	}

	return $flux;
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
	
	if(test_plugin_actif('motsar')){
		$select[] = 'mots_arborescents';
	}
	
	$activation = sql_allfetsel($select, 'spip_groupes_mots', "tables_liees LIKE '$objets'");
	if (count($activation) > 0) {
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
				 $liens = objet_trouver_liens(array($objet => $id), '*');
				 if (isset($liens['rubrique'])) {
				 	$id_rubrique = $liens['id_objet'];
				 }
			}
		}
	}
	
	return $id_rubrique;
}


