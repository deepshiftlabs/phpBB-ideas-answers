<?php
/**
*
* acp_permissions_evaluation [Español]
*
* @package evaluation topics
* @version $Id: permissions_evaluation.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @translated by : Nostromo - nostromo007@gmail.com
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

$lang['permission_cat']['evaluation'] = 'Tema Evaluación';

$lang = array_merge($lang, array(
	'acl_f_evaluation'			=> array('lang' => 'Mayo temas por evaluar', 'cat' => 'evaluation'),
	'acl_f_evaluation_see'		=> array('lang' => 'En caso de que las evaluaciones se mostrarán tema', 'cat' => 'evaluation'),
	'acl_f_evaluation_own'		=> array('lang' => 'Mayo escritores evaluar sus propios temas', 'cat' => 'evaluation'),
	'acl_f_evaluation_change'	=> array('lang' => 'Mayo evaluaciones ser cambiado', 'cat' => 'evaluation'),
));
?>