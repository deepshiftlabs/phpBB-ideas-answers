<?php
/**
*
* evaluation [Español]
*
* @package evaluation topics
* @version $Id: evaluation.php , v 1.0.8 2010-02-13 Novan $
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

$lang = array_merge($lang, array(
	'TOPIC_EVALUATION_RESULT'		=> 'Calificaciones: %u, %.2f on the average.%s',
	'YOUR_EVALUATION1'				=> ' Su evaluación es una estrella',
	'YOUR_EVALUATION2'				=> ' Su evaluación es %u estrellas.',
	'EVALUATE_TOPIC'				=> 'Calificación',
    'ACP_EVALUATION'				=> 'Calificación',
	'ACP_EVALUATION_EXPLAIN'		=> 'Define el número máximo de estrellas para calificar un tema.',
	'TOPIC_EVALUATION'				=> 'Calificación',
	'INSTALLED_EVALUATION_MOD'		=> 'La Evaluación Tema / Categoría Mod se ha instalado correctamente.',
	'DEINSTALLED_EVALUATION_MOD'	=> 'La Evaluación Tema / Categoría Mod. éxito desinstalados.',
	'REMOVE_INSTALL'				=> 'Por favor, borra el directorio Install y el archivo de instalación que contiene.',
));

?>