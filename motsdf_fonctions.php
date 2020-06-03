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
 * Permet de retrouver rapidement l'id_mot affecté à un objet
 *
 * @example
 *  #SET{id_mot, #VAL{formation}|motsdf_id_mot{#ID_FORMATION}}
 * 
 * @api
 * @param string objet
 * @param int id_objet
 *
 * @return int
 */
function motsdf_id_mot($objet, $id_objet) {
	$id_mot = sql_getfetsel('id_mot', 'spip_mots_liens', 'objet='.sql_quote($objet).' AND id_objet='.intval($id_objet));
	
	return $id_mot;
}
