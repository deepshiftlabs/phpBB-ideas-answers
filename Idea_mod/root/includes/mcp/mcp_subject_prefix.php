<?php
/**
*
* @author Erik Frèrejean (erikfrerejean@phpbb.com) http://www.erikfrerejean.nl
*
* @package mcp
* @copyright (c) 2010 Erik Frèrejean
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package mcp
*/
class mcp_subject_prefix
{
	/**
	* Main method, is called by p_master to run the module
	*/
	public function main($mode, $id)
	{
		// Fetch all the data
		$fid	= request_var('f', 0);
		$pid	= request_var('prefixid', 0);
		$red	= request_var('redirect', 'index.' . PHP_EXT);
		$tid	= request_var('t', 0);
		$red	= reapply_sid($red);

		// Get the prefix data
		$tree = $forums = array();
		sp_phpbb::$cache->obtain_prefix_forum_tree($tree, $forums);

		// Nothing for this forum
		if (empty($tree[$fid]))
		{
			return;
		}

		// Fetch the current data for this forum
		$sql = 'SELECT subject_prefix_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $tid;
		$result	= sp_phpbb::$db->sql_query($sql);
		$_c_pid	= sp_phpbb::$db->sql_fetchfield('subject_prefix_id', false, $result);
		sp_phpbb::$db->sql_freeresult($result);

		// No change
		if ($pid == $_c_pid)
		{
			meta_refresh(2, $red);
			trigger_error(sp_phpbb::$user->lang['PREFIX_NOT_CHANGED'] . '<br /><br />' . sprintf(sp_phpbb::$user->lang['RETURN_PAGE'], '<a href="' . $red . '">', '</a>'));
		}

		// The selected prefix can be used in this forum?
		if (!isset($tree[$fid][$pid]) && $pid > 0)
		{
			meta_refresh(2, $red);
			trigger_error(sp_phpbb::$user->lang['PREFIX_NOT_ALLOWED'] . '<br /><br />' . sprintf(sp_phpbb::$user->lang['RETURN_PAGE'], '<a href="' . $red . '">', '</a>'));
		}

		// Update
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET subject_prefix_id = ' . $pid . '
			WHERE topic_id = ' . $tid;
		sp_phpbb::$db->sql_query($sql);
		if (sp_phpbb::$db->sql_affectedrows() == -1)
		{
			trigger_error('PREFIX_UPDATE_FAILED');
		}
		else
		{
			sp_cache::subject_prefix_quick_clear();
			meta_refresh(2, $red);
			trigger_error(sp_phpbb::$user->lang['PREFIX_UPDATED_SUCCESS'] . '<br /><br />' . sprintf(sp_phpbb::$user->lang['RETURN_PAGE'], '<a href="' . $red . '">', '</a>'));
		}
	}
}