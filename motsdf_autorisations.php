<?php
/**
 * Définit les autorisations du plugin Mots dans formulaires
 *
 * @plugin     Mots dans formulaires
 * @copyright  2018
 * @author     Peetdu
 * @licence    GNU/GPL
 * @package    SPIP\Motsdf\Autorisations
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Fonction d'appel pour le pipeline
 * @pipeline autoriser */
function motsdf_autoriser() {
}


/**
 * Fonction interne : SURCHARGE depuis plugin Motus
 * Retourne vrai si la rubrique $id fait partie d'une des branches de $opt['rubriques_on']
 * 
 * Autrement dit, si la rubrique appartient à une des rubriques données
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_groupemots_dansrubrique($faire,$type,$id,$qui,$opt){
	static $rubriques = array();

	if (!isset($opt['rubriques_on'])
	or !$rubs = $opt['rubriques_on']  // pas de liste de rubriques ?
	or !$id  // pas d'info de rubrique... on autorise par defaut...
	or in_array($id, $rubs)) // la rubrique est dedans
		return true;

	// la ca se complique...
	// si deja calcule... on le retourne.
	$hash = md5(implode('',$rubs));
	if (isset($rubriques[$id][$hash]))
		return $rubriques[$id][$hash];
	
	// remonter recursivement les rubriques...
	$id_parent = sql_getfetsel('id_parent','spip_rubriques', 'id_rubrique = '. sql_quote($id));

	// si racine... pas de chance
	if (!$id_parent) {
		$rubriques[$id][$hash] = false;
	} else {
		$rubriques[$id][$hash] = autoriser('dansrubrique','groupemots',$id_parent,$qui,$opt);
	}

	return $rubriques[$id][$hash];
}
