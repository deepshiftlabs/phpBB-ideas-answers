<?php
/**
*
* @package evaluation topics
* @version $Id: acp_forums.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 Ren Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function evaluation_request_data(&$forum_data, $request = false)
{
	$forum_data += array('forum_evaluation' => (($request) ? request_var('evaluation', 0) : 0));
}

function evaluation_extend_template($forum_data)
{
	global $template, $user;

	$user->add_lang('mods/evaluation');
	$template->assign_var('S_EVALUATION_ACTIVATED', $forum_data['forum_evaluation']);
}
?>