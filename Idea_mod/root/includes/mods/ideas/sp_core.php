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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * The main Subject Prefix class
 */
abstract class sp_core
{
	static public function init()
	{
		// Define the database tables
		global $table_prefix;
		define('SUBJECT_PREFIX_TABLE', $table_prefix . 'subject_prefixes');
		define('SUBJECT_PREFIX_FORUMS_TABLE', $table_prefix . 'subject_prefix_forums');

		// We're going to need this data anyways, better to have the cache class fetch it now
		sp_phpbb::$cache->obtain_subject_prefixes();

		// Add some language files
		if (sp_phpbb::$user->page['page_dir'] == 'adm' && sp_phpbb::$user->page['page_name'] == 'index.' . PHP_EXT)
		{
			// Include the permissions file
			sp_phpbb::$user->add_lang('mods/ideas/permissions_subject_prefix');
		}

		// Include the acp langauge file
		sp_phpbb::$user->add_lang('mods/ideas/subject_prefix_common');
	}

	/**
	 * Generate the options for any dropdown box containing prefixes
	 * @param	Integer	$fid		The box is used in this forum
	 * @param	Integer	$selected	The ID of the selected option
	 * @return	String				String containing the options HTML
	 */
	static public function generate_prefix_options($fid, $selected = false)
	{
		// Fetch all the prefixes
		$tree = $forums = array();
		sp_phpbb::$cache->obtain_prefix_forum_tree($tree, $forums);

		// No prefixes in this forum
		if (empty($tree[$fid]))
		{
			return '';
		}

		$options = array("<option value='0'" . (($selected === false) ? " selected='selected'" : '') . '>' . sp_phpbb::$user->lang('SELECT_A_PREFIX') . '</option>');
		foreach ($tree[$fid] as $prefix)
		{
			$options[] = "<option value='{$prefix['prefix_id']}'" . (($selected == $prefix['prefix_id']) ? " selected='selected'" : '') . " style='color: {$prefix['prefix_colour']};'>" . self::generate_prefix_string($prefix['prefix_id'], false) . '</option>';
		}

		return implode('', $options);
	}

	/**
	 * Generate the output for the reqested prefix
	 * @param	Integer	$pid	ID of the prefix
	 * @param	Boolean	$markup	Use colouring or not
	 * @return	void|String		Formatted string
	 */
	static public function generate_prefix_string($pid, $markup = true)
	{
		static $formatted	= '<span style="color: #%s; background-color: #%s; padding: 1px 2px; -moz-border-radius: 3px 3px 3px 3px;">%s</span>';
		static $unformatted	= '%s';

		$prefixes = sp_phpbb::$cache->obtain_subject_prefixes();

		// Doesn't exist
		if (!isset($prefixes[$pid]))
		{
			return;
		}

		$prefix_title = (isset(sp_phpbb::$user->lang['SP_' . $prefixes[$pid]['title']])) ? sp_phpbb::$user->lang['SP_' . $prefixes[$pid]['title']] : $prefixes[$pid]['title'];

		if ($markup)
		{
			return sprintf($formatted, $prefixes[$pid]['colour'], $prefixes[$pid]['bgcolour'], $prefix_title);
		}
		else
		{
			return sprintf($unformatted, $prefix_title);
		}
	}

	/**
	 * Add a prefix
	 * @param	String	$prefix_title	The title of the new prefix
	 * @param	String	$prefix_colour	The colour of the new prefix
	 * @param	Array	$forums			Array containing the forums to which this prefix will be added
	 * @param	Array	$error			Array that will be filled with encountered error messages
	 * @return	Integer|void			The ID of the new prefix or void on error
	 */
	static public function prefix_add($prefix_title, $prefix_colour, $prefix_bgcolour, $forums, &$error)
	{
		// Validate input
		$error = self::_validate_input($prefix_title, $prefix_colour, $prefix_bgcolour);

		if (!empty($error))
		{
			return;
		}

		// Fix colour code
		$prefix_colour = (substr($prefix_colour, 0, 1) == '#') ? substr($prefix_colour, 1) : $prefix_colour;

		// Create the actual prefix
		sp_phpbb::$db->sql_query('INSERT INTO ' . SUBJECT_PREFIX_TABLE . ' ' . sp_phpbb::$db->sql_build_array('INSERT', array(
			'prefix_title'	=> $prefix_title,
			'prefix_colour'	=> $prefix_colour,
            'prefix_bgcolour'	=> $prefix_bgcolour,
		)));

		$pid = sp_phpbb::$db->sql_nextid();

		if ($pid === false)
		{
			$error[] = 'PREFIX_INSERT_FAIL';
			return;
		}

		self::prefix_link_to_forums($pid, $forums, $error);

		sp_cache::subject_prefix_quick_clear();

		return $pid;
	}

	static public function prefix_update($pid, $prefix_title, $prefix_colour, $prefix_bgcolour, $forums, &$error)
	{
		if ($pid == 0)
		{
			$pid = self::prefix_add($prefix_title, $prefix_colour, $prefix_bgcolour, $forums, $error);
			return $pid;
		}

		// Validate input
		$error = self::_validate_input($prefix_title, $prefix_colour, $prefix_bgcolour);

		if (!empty($error))
		{
			return;
		}

		// Fix colour code
		$prefix_colour = (substr($prefix_colour, 0, 1) == '#') ? substr($prefix_colour, 1) : $prefix_colour;
        $prefix_bgcolour = (substr($prefix_bgcolour, 0, 1) == '#') ? substr($prefix_bgcolour, 1) : $prefix_bgcolour;

		// Update the prefix
		sp_phpbb::$db->sql_query('UPDATE ' . SUBJECT_PREFIX_TABLE . ' SET ' . sp_phpbb::$db->sql_build_array('UPDATE', array(
			'prefix_title'	=> $prefix_title,
			'prefix_colour'	=> $prefix_colour,
            'prefix_bgcolour'	=> $prefix_bgcolour,
		)) . ' WHERE prefix_id = ' . $pid);

		// Just for the ease of things we remove all prefix - forum links here
		// And than add them all back
		sp_phpbb::$db->sql_query('DELETE FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . ' WHERE prefix_id = ' . $pid);

		self::prefix_link_to_forums($pid, $forums, $error);

		sp_cache::subject_prefix_quick_clear();
	}

	/**
	 * Completely delete a prefix
	 * @param	Boolean|Integer	$pid	The prefix to be deleted, if false it will be grapped from the $_REQUEST array by "pid"
	 * @return	void
	 */
	static public function prefix_delete($pid = false)
	{
		if (!ctype_digit($pid))
		{
			$pid = request_var('pid', 0);
		}

		foreach (array(SUBJECT_PREFIX_FORUMS_TABLE, SUBJECT_PREFIX_TABLE) as $table)
		{
			sp_phpbb::$db->sql_query("DELETE FROM $table WHERE prefix_id = $pid");
		}

		// Update the topics table
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET subject_prefix_id = 0
			WHERE subject_prefix_id = ' . $pid;
		sp_phpbb::$db->sql_query($sql);

		sp_cache::subject_prefix_quick_clear();
	}

	/**
	 * Delete a prefix from a given forum
	 * @param	Integer	$pid	The prefix ID
	 * @param	Integer	$fid	The forum ID
	 * @return	void
	 */
	static public function prefix_delete_forum($pid, $fid)
	{
		// Remove for this forum
		$sql = 'DELETE FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . '
			WHERE prefix_id = ' . $pid . '
				AND forum_id = ' . $fid;
		sp_phpbb::$db->sql_query($sql);

		// Update the topics table
		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET subject_prefix_id = 0
			WHERE forum_id = $fid
				AND subject_prefix_id = $pid";
		sp_phpbb::$db->sql_query($sql);

		// A prefix can't exist when its not linked to at least one forum
		$result	= sp_phpbb::$db->sql_query('SELECT forum_id FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . ' WHERE prefix_id = ' . $pid);
		$check	= sp_phpbb::$db->sql_fetchfield('forum_id', false, $result);
		sp_phpbb::$db->sql_freeresult($result);

		if (!empty($check))
		{
			sp_cache::subject_prefix_quick_clear();
			return;
		}

		// Delete it
		self::prefix_delete($pid);
	}

	/**
	 * Move a given prefix up/down in the tree
	 * @param	Integer	$fid			The ID defining the forum in which this move will occure
	 * @param	Integer	$field_order	The current order inside this forum
	 * @param	String	$direction		String defining the direction (up or down)
	 * @return	void
	 */
	static public function prefix_reorder($fid, $field_order, $direction)
	{
		$order_total = $field_order * 2 + (($direction == 'up') ? -1 : 1);

		$sql = 'UPDATE ' . SUBJECT_PREFIX_FORUMS_TABLE . "
			SET prefix_order = $order_total - prefix_order
			WHERE prefix_order IN ($field_order, " . (($direction == 'up') ? $field_order - 1 : $field_order + 1) . ')
				AND forum_id = ' . $fid;
		sp_phpbb::$db->sql_query($sql);

		sp_cache::subject_prefix_quick_clear();
	}

	/**
	 * Resyncronise the prefix tables.
	 * * Make sure the prefix orders are incrementing numbers
	 * * A prefix can't exist when its not linked to at least 1 forum
	 *
	 * @return void
	 */
	static public function prefix_order_resync()
	{
		// First clear the cache, need to make sure we get the data as it is right now in the database
		sp_cache::subject_prefix_quick_clear();

		// Fetch the tree
		$tree = $forums = array();
		sp_phpbb::$cache->obtain_prefix_forum_tree($tree, $forums);

		sp_phpbb::$db->sql_transaction('begin');

		// Run through the tree and fix the ordering
		foreach ($tree as $fid => $data)
		{
			$next_order = 0;

			// Go through the data and check the order
			foreach ($data as $prefix)
			{
				// Order as expected?
				if ($prefix['prefix_order'] != $next_order)
				{
					// Update the field
					$sql = 'UPDATE ' . SUBJECT_PREFIX_FORUMS_TABLE . "
						SET prefix_order = $next_order
						WHERE prefix_id = {$prefix['prefix_id']}
							AND forum_id = $fid";
					sp_phpbb::$db->sql_query($sql);
				}

				$next_order++;
			}
		}

		// Now remove all prefixes that aren't linked to a forum
		$pids	= array();
		$sql	= 'SELECT prefix_id
			FROM ' . SUBJECT_PREFIX_FORUMS_TABLE;
		$result	= sp_phpbb::$db->sql_query($sql);
		while ($row = sp_phpbb::$db->sql_fetchrow($result))
		{
			$pids[] = $row['prefix_id'];
		}
		sp_phpbb::$db->sql_freeresult($result);

		sp_phpbb::$db->sql_query('DELETE FROM ' . SUBJECT_PREFIX_TABLE . ' WHERE ' . sp_phpbb::$db->sql_in_set('prefix_id', $pids, true));

		sp_phpbb::$db->sql_transaction('commit');

		sp_cache::subject_prefix_quick_clear();
	}

	/**
	 * Link a prefix to forums
	 * @param	Integer		$pid	The prefix ID
	 * @param	Array		$forums	A list containing all forum IDs to which this prefix will be linked
	 * @param	Array		$error	Array that will be filled with encountered error messages
	 * @return	Boolean
	 */
	static public function prefix_link_to_forums($pid, $forums, &$error)
	{
		if (!is_array($forums))
		{
			$forums = array($forums);
		}

		// When this prefix is already linked to this forum we'll leave it there
		$sql = 'SELECT forum_id
			FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . "
			WHERE prefix_id = $pid
				AND " . sp_phpbb::$db->sql_in_set('forum_id', $forums);
		$forums = array_flip($forums);	// Flip for the ease of things here
		$result = sp_phpbb::$db->sql_query($sql);
		while($row = sp_phpbb::$db->sql_fetchrow($result))
		{
			if (isset($forums[$row['forum_id']]))
			{
				unset($forums[$row['forum_id']]);
			}
		}
		$forums = array_flip($forums);	// Flip back

		// No more forums
		if (empty($forums))
		{
			return;
		}

		// First got to figure out where in the tree this pid has to be added
		$max_orders = array();
		$sql	= 'SELECT forum_id, MAX(prefix_order) AS prefix_order
			FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . '
			WHERE ' . sp_phpbb::$db->sql_in_set('forum_id', $forums) . '
				GROUP BY forum_id';
		$result	= sp_phpbb::$db->sql_query($sql);
		while ($row = sp_phpbb::$db->sql_fetchrow($result))
		{
			$max_orders[$row['forum_id']] = $row['prefix_order'];
		}
		sp_phpbb::$db->sql_freeresult($result);

		// Now prepare the data to be inserted
		$insert_data = array();
		foreach ($forums as $forum)
		{
			$insert_data[]	= array(
				'prefix_id'		=> $pid,
				'forum_id'		=> $forum,
				'prefix_order'	=> (isset($max_orders[$forum])) ? $max_orders[$forum] + 1 : 0,
			);
		}

		// Insert
		sp_phpbb::$db->sql_multi_insert(SUBJECT_PREFIX_FORUMS_TABLE, $insert_data);
	}

	static private function _validate_input($prefix_title, $prefix_colour, $prefix_bgcolour)
	{
		$error = array();

		if (empty($prefix_title))
		{
			$error[] = 'NO_PREFIX_TITLE';
		}
		if (!preg_match('~^(#{0,1})[a-fA-F0-9]{6}$~', $prefix_colour))
		{
			$error[] = 'NO_PREFIX_COLOUR';
		}
        if (!preg_match('~^(#{0,1})[a-fA-F0-9]{6}$~', $prefix_bgcolour))
		{
			$error[] = 'NO_PREFIX_BGCOLOUR';
		}
		return $error;
	}
}
