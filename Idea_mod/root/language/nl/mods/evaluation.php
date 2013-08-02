<?php
/**
*
* evaluation [Nederlands - nl]
*
* @package evaluation topics
* @version $Id: evaluation.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @translated by : Luuk - luukware@home.nl
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'TOPIC_EVALUATION_RESULT'		=> 'Waardering: %u, %.2f gemiddeld.%s',
	'YOUR_EVALUATION1'				=> ' Uw beoordeling is een ster.',
	'YOUR_EVALUATION2'				=> ' Uw beoordeling is %u sterren.',
	'EVALUATE_TOPIC'				=> 'Onderwerp waarderen',
	'ACP_EVALUATION'				=> 'Maximale waardering',
	'ACP_EVALUATION_EXPLAIN'		=> 'Het maximale aantal sterren waarmee gebruikers een onderwerp kunnen waarderen.',
	'TOPIC_EVALUATION'				=> 'Onderwerp waardering',
	'INSTALLED_EVALUATION_MOD'		=> 'Het Onderwerp Evaluatie / Beoordeling Mod is met succes geïnstalleerd.',
	'DEINSTALLED_EVALUATION_MOD'	=> 'Het Onderwerp Evaluatie / Beoordeling Mod is succesvol deinstalled.',
	'REMOVE_INSTALL'				=> 'Verwijder de installatie directory en deze installatie in het bestand.',
));

?>