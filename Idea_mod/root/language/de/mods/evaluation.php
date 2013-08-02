<?php
/**
*
* evaluation [Deutsch — Du]
*
* @package evaluation topics
* @version $Id: evaluation.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'TOPIC_EVALUATION_RESULT'		=> 'Bewertungen: %u, %.2f durchschnittlich.%s',
	'YOUR_EVALUATION1'				=> ' Deine Bewertung liegt bei einem Stern.',
	'YOUR_EVALUATION2'				=> ' Deine Bewertung liegt bei %u Sternen.',
	'EVALUATE_TOPIC'				=> 'Thema bewerten',
	'ACP_EVALUATION'				=> 'Anzahl der Bewertungssterne',
	'ACP_EVALUATION_EXPLAIN'		=> 'Definiert, wie viele Sterne in diesem Forum zum bewerten von Themen angezeigt werden sollen.',
	'TOPIC_EVALUATION'				=> 'Themenbewertung',
	'INSTALLED_EVALUATION_MOD'		=> 'Der Topic Evaluation/Rating Mod wurde erfolgreich installiert.',
	'DEINSTALLED_EVALUATION_MOD'	=> 'Der Topic Evaluation/Rating Mod wurde erfolgreich deinstalliert.',
	'REMOVE_INSTALL'				=> 'Bitte lösche nun dieses Installationsverzeichnis und die Dateien in ihm.',
));

?>