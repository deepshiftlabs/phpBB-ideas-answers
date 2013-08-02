<?php
/**
*
* evaluation [English]
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
    'TOPIC_EVALUATION_RESULT'		=> 'Total rate: %d.%s',
    'TOPIC_EVALUATION_RESULT_LBL'   => 'Rate',
	'YOUR_EVALUATION1'				=> ' Your evaluation is positive.',
	'YOUR_EVALUATION2'				=> ' Your evaluation is negative.',
    'TOPIC_NOT_EVALUATED'           => 'This idea has not need voted yet.',
	'EVALUATE_TOPIC'				=> 'Topic evaluate',
	'TOPIC_EVALUATION'				=> 'Topic evaluation',
	'INSTALLED_EVALUATION_MOD'		=> 'The Topic Evaluation/Rating Mod was successfully installed.',
	'DEINSTALLED_EVALUATION_MOD'	=> 'The Topic Evaluation/Rating Mod was successfully deinstalled.',
	'REMOVE_INSTALL'				=> 'Please delete the install directory and the installation file in it.',
    'ACP_EVALUATION'                => 'This forum will be used as Ideas',
    'ACP_EVALUATION_EXPLAIN'        => 'Each topic in this forum can be evaluated with positive or negative grade'
));

?>