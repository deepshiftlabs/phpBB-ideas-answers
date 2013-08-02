<?php
/**
 *
 * @package Subject Prefix
 * @copyright (c) 2010 Erik Frèrejean ( erikfrerejean@phpbb.com ) http://www.erikfrerejean.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Permission check
if (!isset($user->data['session_admin']) || !$user->data['session_admin'] || $auth->acl_get('!a_subject_prefix'))
{
	trigger_error('NO_AUTH_ADMIN');
}

// Set some common vars
$mode	= request_var('ajax_mode', '');
$result	= 'fail';

switch ($mode)
{
	case 'move' :
		// Get the table
		$tablename	= request_var('tablename', '');

		// Fetch the posted list
		$prefixlist = request_var($tablename, array(0 => ''));

		// Run through the list
		foreach ($prefixlist as $order => $prefix)
		{
			// First one is the header, skip it
			if ($order == 0)
			{
				continue;
			}

			// Get the order nr
			$prefix = substr($prefix, 0, 1);

			// Keep the header in mind
			$order = $order - 1;

			// Update in the db
			sp_phpbb::$db->sql_query('UPDATE ' . SUBJECT_PREFIX_FORUMS_TABLE . ' SET prefix_order = ' . $order . ' WHERE prefix_id = ' . (int) $prefix);
		}

		// Tell the template we're good ^^
		$result = 'success';
	break;
/*
 * Doesn't work correctly atm
	case 'delete' :
		$data = request_var('data', 'pf0_0');

		// Strip "pf"
		$data = substr($data, 3);

		// Strip the ID
		$parts = explode('_', $data);

		sp_core::prefix_delete_forum((int) $parts[0], (int) $parts[1]);

		// Tell the template we're good ^^
		$result = 'success';
	break;
 */
}

// Purge the cache
sp_cache::subject_prefix_quick_clear();

// echo the result
echo $result;

garbage_collection();
exit_handler();
