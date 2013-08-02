<?php
/**
*
* evaluation [Arabic]
*
* @package evaluation topics
* @version $Id: evaluation.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @translated by : FaresNB - http://www.art-en.com/forum/
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
	'TOPIC_EVALUATION_RESULT'		=> 'قيّم الموضوع: %u , معدّل التقييم: %.2f.%s',
	'YOUR_EVALUATION1'				=> ' تقييمكم نجمة واحدة.',
	'YOUR_EVALUATION2'				=> ' هو تقييمكم %u نجوم ش.',
	'EVALUATE_TOPIC'				=> 'تقييم الموضوع',
    'ACP_EVALUATION'				=> 'عدد نجوم التقييم',
	'ACP_EVALUATION_EXPLAIN'		=> 'حدد العدد الأقصى لنجوم التقييم',
	'TOPIC_EVALUATION'				=> 'تقييم الموضوع',
	'INSTALLED_EVALUATION_MOD'		=> 'التقييم الموضوع / تصنيف وزارة الدفاع تثبيت بنجاح.',
	'DEINSTALLED_EVALUATION_MOD'	=> 'التقييم الموضوع / تصنيف وزارة الدفاع تم بنجاح حذف.',
	'REMOVE_INSTALL'				=> 'الرجاء حذف تثبيت هذا دليل وتركيب الملفات فيه',
));

?>