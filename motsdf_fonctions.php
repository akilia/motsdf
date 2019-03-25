<?php
/**
 * Fonctions utiles au plugin Mots dans formulaires
 *
 * @plugin     Mots dans formulaires
 * @copyright  2018
 * @author     Peetdu
 * @licence    GNU/GPL
 * @package    SPIP\Motsdf\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/motsdf_api');


/**
 * Vérifier si le plugin rang est actif et si oui, qu'il a ete active sur les mots cles
 * Permet par exemple d'utiliser ou non le critere {par rang}
 *
 * @param int $id_mot
 * 
 * @param int $id_objet
 * 
 * @return bool
 *     true si Rang a été active sur les mots-cles
**/
function motsdf_test_rang_actif($objet) {

	$table = table_objet_sql($objet);
	$table_sql = lister_tables_objets_sql($table);
	if (isset($table_sql['field']['rang'])) {
		return true;
	} 
	else {
		return false;
	}
}
