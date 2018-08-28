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
 * Vérifier que le mot-clé est associé
 *
 * @param int $id_mot
 * 
 * @param int $id_objet
 * 
 * @return bool
 *     true si le mot clé est associé
**/
function motsdf_mot_select($id_mot, $id_objet, $objet){
	$id_mot = intval($id_mot);
	$id_objet = intval($id_objet);
	$res = sql_countsel('spip_mots_liens', array("id_mot=$id_mot", "id_objet=$id_objet", "objet=".sql_quote($objet)));
	if ($res > 0 ) return true;
	else return false;
}