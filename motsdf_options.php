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

$opt = array('rubriques_on' => array(0=>2, 1=>14));
debug($opt);
$blah = autoriser('dansrubrique', 'groupemots', '14', '1', $opt);
debug($blah);
