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

// When the MOD isn't installed, we won't bother here ^^
if (!isset($config['subject_prefix_version']))
{
	return;
}

/**
 * Class that contains all hooked methods
 */
abstract class sp_hook
{
	/**
	 * Register all subject prefix hooks
	 * @param	phpbb_hook	$phpbb_hook	The phpBB hook object
	 * @return	void
	 */
	static public function register(&$phpbb_hook)
	{
		$phpbb_hook->register('phpbb_user_session_handler', 'sp_hook::subject_prefix_init');
		$phpbb_hook->register(array('template', 'display'), 'sp_hook::add_subject_prefix_to_page');
		$phpbb_hook->register(array('template', 'display'), 'sp_hook::subject_prefix_template_hook');
	}

	/**
	 * A hook that is used to initialise the Subject Prefix core
	 * @param	phpbb_hook	$phpbb_hook	The phpBB hook object
	 * @return	void
	 */
	static public function subject_prefix_init(&$hook)
	{
		// Load the phpBB class
		if (!class_exists('sp_phpbb'))
		{
			global $phpbb_root_path, $phpEx;
			require($phpbb_root_path . 'includes/mods/ideas/sp_phpbb.' . $phpEx);
			sp_phpbb::init();
		}

		// Load the Subject Prefix cache
		if (!class_exists('sp_cache'))
		{
			require PHPBB_ROOT_PATH . 'includes/mods/ideas/sp_cache.' . PHP_EXT;
		}

		// Load the Subject Prefix core
		if (!class_exists('sp_core'))
		{
			require PHPBB_ROOT_PATH . 'includes/mods/ideas/sp_core.' . PHP_EXT;
			sp_core::init();
		}
	}

	/**
	 * A hook that adds the subject prefixes to phpBB pages without modifying the page itself
	 * @param	phpbb_hook	$phpbb_hook	The phpBB hook object
	 * @return	void
	 * @todo	Change this method to a more flexible one. The current method is really
	 * 			static and there is a lot of code duplication around here.
	 */
	static public function add_subject_prefix_to_page(&$hook)
	{
		// Only on regular pages
		if (!empty(sp_phpbb::$user->page['page_dir']))
		{
			return;
		}

		// This is kinda nasty, viewforum.php?f=. should be handled as index.php
		if (sp_phpbb::$user->page['page_name'] == 'viewforum.' . PHP_EXT && isset(sp_phpbb::$template->_tpldata['forumrow']))
		{
			sp_phpbb::$user->page['page_name'] = 'index.' . PHP_EXT;
		}

		// Add the prefix to certain pages
		switch (sp_phpbb::$user->page['page_name'])
		{
			case 'index.' . PHP_EXT :
				if (empty(sp_phpbb::$template->_tpldata['forumrow']))
				{
					return;
				}

				// This MOD also supports Joas his "last post topic title MOD".
				if (isset(sp_phpbb::$config['altt_active']) && sp_phpbb::$config['altt_active'])
				{
					$blockvar = 'ALTT_LINK_NAME_SHORT';
				}
				else
				{
					$blockvar = 'LAST_POST_SUBJECT';
				}

				// To fetch the subject prefixes we'll need the last post ids
				$last_post_ids = array();
				foreach (sp_phpbb::$template->_tpldata['forumrow'] as $row => $data)
				{
					// Need the last post link
					if (empty($data['U_LAST_POST']))
					{
						continue;
					}

					$last_post_ids[$row] = substr(strrchr($data['U_LAST_POST'], 'p'), 1);
				}

				// Get the prefixes
				$sql = 'SELECT topic_last_post_id, subject_prefix_id
					FROM ' . TOPICS_TABLE . '
					WHERE ' . sp_phpbb::$db->sql_in_set('topic_last_post_id', $last_post_ids);
				$result	= sp_phpbb::$db->sql_query($sql);
				$last_post_ids = array_flip($last_post_ids);
				while ($row = sp_phpbb::$db->sql_fetchrow($result))
				{
					//$last_post_subject = sp_core::generate_prefix_string($row['subject_prefix_id']) . ' ' . sp_phpbb::$template->_tpldata['forumrow'][$last_post_ids[$row['topic_last_post_id']]][$blockvar];
                    $last_post_subject = sp_phpbb::$template->_tpldata['forumrow'][$last_post_ids[$row['topic_last_post_id']]][$blockvar] . ' ' . sp_core::generate_prefix_string($row['subject_prefix_id']);
					// Alter the array
					sp_phpbb::$template->alter_block_array('forumrow', array(
						$blockvar => $last_post_subject,
					), $key = $last_post_ids[$row['topic_last_post_id']], 'change');
				}
				sp_phpbb::$db->sql_freeresult($result);
			break;

			case 'mcp.' . PHP_EXT :

			break;

			case 'memberlist.' . PHP_EXT :
				// Most active topic
				if (!empty(sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC']))
				{
					// Strip the topic id from link
					$topic_id = substr(strrchr(sp_phpbb::$template->_tpldata['.'][0]['U_ACTIVE_TOPIC'], '='), 1);

					// Get the subject prefix
					$sql = 'SELECT subject_prefix_id
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id = ' . (int) $topic_id;
					$result	= sp_phpbb::$db->sql_query($sql);
					$pid	= sp_phpbb::$db->sql_fetchfield('subject_prefix_id', false, $result);
					sp_phpbb::$db->sql_freeresult($result);

					// Send to the template
					//$active_title = sp_core::generate_prefix_string($pid) . ' ' . sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC'];
                    $active_title = sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC'] . ' ' . sp_core::generate_prefix_string($pid);
					sp_phpbb::$template->assign_var('ACTIVE_TOPIC', $active_title);
				}
			break;

			case 'posting.' . PHP_EXT :
				global $preview;

				// When previewing this add the tag
				if (!empty($preview))
				{
					$pid = request_var('subjectprefix', 0);
					$topic_title = sp_phpbb::$template->_tpldata['.'][0]['TOPIC_TITLE'];
					//$topic_title = sp_core::generate_prefix_string($pid) . ' ' . $topic_title;
                    $topic_title .= ' ' . sp_core::generate_prefix_string($pid);
					sp_phpbb::$template->assign_var('TOPIC_TITLE', $topic_title);
				}
			break;

			case 'search.' . PHP_EXT :
				if (empty(sp_phpbb::$template->_tpldata['searchresults']))
				{
					return;
				}

				$sr = request_var('sr', '');

				// Collect the post ids
				$row_id = array();
				foreach (sp_phpbb::$template->_tpldata['searchresults'] as $row => $data)
				{
					if ($sr == 'topics')
					{
						if (empty($data['U_VIEW_TOPIC']))
						{
							continue;
						}

						$row_id[$row] = (int) substr(strrchr(sp_phpbb::$template->_tpldata['searchresults'][$row]['U_VIEW_TOPIC'], '='), 1);
					}
					else
					{
						if (empty($data['POST_ID']) && empty($data['TOPIC_ID']))
						{
							continue;
						}

						$row_id[$row] = ($data['POST_ID'] ? $data['POST_ID'] : $data['TOPIC_ID']);
					}
				}
                if(!count($row_id)) {
                    return;
                }
				// Fetch the prefixes
				switch ($sr)
				{
					case 'topics' :
						$sql = 'SELECT topic_id, subject_prefix_id
							FROM ' . TOPICS_TABLE . '
							WHERE ' . sp_phpbb::$db->sql_in_set('topic_id', $row_id);
						$rowfield = 'topic_id';
					break;

					default :
						$sql = 'SELECT p.post_id, t.subject_prefix_id
							FROM (' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t)
							WHERE ' . sp_phpbb::$db->sql_in_set('p.post_id', $row_id) . '
								AND t.topic_id = p.topic_id';
						$rowfield = 'post_id';
				}
				$result = sp_phpbb::$db->sql_query($sql);
				$row_id = array_flip($row_id);
				while ($row = sp_phpbb::$db->sql_fetchrow($result))
				{
					//$topic_title = sp_core::generate_prefix_string($row['subject_prefix_id']) . ' ' . sp_phpbb::$template->_tpldata['searchresults'][$row_id[$row[$rowfield]]]['TOPIC_TITLE'];
                    $topic_title = sp_phpbb::$template->_tpldata['searchresults'][$row_id[$row[$rowfield]]]['TOPIC_TITLE'] . ' ' . sp_core::generate_prefix_string($row['subject_prefix_id']);
					// Update the template
					sp_phpbb::$template->alter_block_array('searchresults', array(
						'TOPIC_TITLE' => $topic_title,
					), $row_id[$row[$rowfield]], 'change');
				}
				sp_phpbb::$db->sql_freeresult($result);
			break;

			case 'ucp.' . PHP_EXT :
				// Bookmarks and subscriptions
				if (!empty(sp_phpbb::$template->_tpldata['topicrow']))
				{
					$topic_ids_rows = array();
					foreach (sp_phpbb::$template->_tpldata['topicrow'] as $row => $data)
					{
						$topic_ids_rows[$row] = $data['TOPIC_ID'];
					}

					$sql = 'SELECT topic_id, subject_prefix_id
						FROM ' . TOPICS_TABLE . '
						WHERE ' . sp_phpbb::$db->sql_in_set('topic_id', $topic_ids_rows) . '
							AND subject_prefix_id > 0';
					$result = sp_phpbb::$db->sql_query($sql);
					$topic_ids_rows = array_flip($topic_ids_rows);
					while ($row = sp_phpbb::$db->sql_fetchrow($result))
					{
						//$topic_title = sp_core::generate_prefix_string($row['subject_prefix_id']) . ' ' . sp_phpbb::$template->_tpldata['topicrow'][$topic_ids_rows[$row['topic_id']]]['TOPIC_TITLE'];
                        $topic_title = sp_phpbb::$template->_tpldata['topicrow'][$topic_ids_rows[$row['topic_id']]]['TOPIC_TITLE'] . ' ' . sp_core::generate_prefix_string($row['subject_prefix_id']);
                            
						// Alter the array
						sp_phpbb::$template->alter_block_array('topicrow', array(
							'TOPIC_TITLE' => $topic_title,
						), $key = $topic_ids_rows[$row['topic_id']], 'change');
					}
					sp_phpbb::$db->sql_freeresult($result);
				}

				// Most active topic
				if (!empty(sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC']))
				{
					// Strip the topic id from link
					$topic_id = substr(strrchr(sp_phpbb::$template->_tpldata['.'][0]['U_ACTIVE_TOPIC'], '='), 1);

					// Get the subject prefix
					$sql = 'SELECT subject_prefix_id
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id = ' . (int) $topic_id;
					$result	= sp_phpbb::$db->sql_query($sql);
					$pid	= sp_phpbb::$db->sql_fetchfield('subject_prefix_id', false, $result);
					sp_phpbb::$db->sql_freeresult($result);

					// Send to the template
					//$active_title = sp_core::generate_prefix_string($pid) . ' ' . sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC'];
                    $active_title = sp_phpbb::$template->_tpldata['.'][0]['ACTIVE_TOPIC'] . ' ' . sp_core::generate_prefix_string($pid);
					sp_phpbb::$template->assign_var('ACTIVE_TOPIC', $active_title);
				}
			break;

			case 'viewforum.' . PHP_EXT :
				// As the topic data is unset once its used we'll have to introduce an query to
				// fetch the prefixes
				if (empty(sp_phpbb::$template->_tpldata['topicrow']))
				{
					return;
				}

				$topic_ids_rows = array();
				foreach (sp_phpbb::$template->_tpldata['topicrow'] as $row => $data)
				{
					$topic_ids_rows[$row] = $data['TOPIC_ID'];
				}

				$sql = 'SELECT topic_id, subject_prefix_id
					FROM ' . TOPICS_TABLE . '
					WHERE ' . sp_phpbb::$db->sql_in_set('topic_id', $topic_ids_rows) . '
						AND subject_prefix_id > 0';
				$result = sp_phpbb::$db->sql_query($sql);
				$topic_ids_rows = array_flip($topic_ids_rows);
				while ($row = sp_phpbb::$db->sql_fetchrow($result))
				{
					//$topic_title = sp_core::generate_prefix_string($row['subject_prefix_id']) . ' ' . sp_phpbb::$template->_tpldata['topicrow'][$topic_ids_rows[$row['topic_id']]]['TOPIC_TITLE'];
                    $topic_title = sp_phpbb::$template->_tpldata['topicrow'][$topic_ids_rows[$row['topic_id']]]['TOPIC_TITLE'];
                    $topic_title .= ' ' . sp_core::generate_prefix_string($row['subject_prefix_id']);
                    
					// Alter the array
					sp_phpbb::$template->alter_block_array('topicrow', array(
						'TOPIC_TITLE' => $topic_title,
					), $key = $topic_ids_rows[$row['topic_id']], 'change');
				}
				sp_phpbb::$db->sql_freeresult($result);
			break;

			case 'viewtopic.' . PHP_EXT :
				global $forum_id, $topic_id;
				global $viewtopic_url, $topic_data;

				// Add to the page title
				/*$page_title = sp_phpbb::$template->_tpldata['.'][0]['PAGE_TITLE'];
				$page_title = substr_replace($page_title, sp_core::generate_prefix_string($topic_data['subject_prefix_id'], false), strpos($page_title, '-') + 1, 0);
				sp_phpbb::$template->assign_var('PAGE_TITLE', $page_title);*/

				// Add to the topic title
				$topic_title = sp_phpbb::$template->_tpldata['.'][0]['TOPIC_TITLE'];
				$topic_title .= ' ' . sp_core::generate_prefix_string($topic_data['subject_prefix_id']);
				sp_phpbb::$template->assign_var('TOPIC_TITLE', $topic_title);

				// The quick MOD box
				if (sp_phpbb::$auth->acl_get('m_subject_prefix', $forum_id))
				{
					sp_phpbb::$template->assign_vars(array(
						'S_SUBJECT_PREFIX_QUICK_MOD'		=> sp_core::generate_prefix_options($forum_id, $topic_data['subject_prefix_id']),
						'S_SUBJECT_PREFIX_QUICK_MOD_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'mcp.' . PHP_EXT, array('i' => 'subject_prefix', 'mode' => 'quick_edit', 'f' => $forum_id, 't' => $topic_id, 'redirect' => urlencode(str_replace('&amp;', '&', $viewtopic_url))), true, sp_phpbb::$user->session_id),
					));
				}
			break;
		}
	}

	/**
	 * A hook that is used to change the behavior of phpBB just before the templates
	 * are displayed.
	 * @param	phpbb_hook	$phpbb_hook	The phpBB hook object
	 * @return	void
	 * @todo	Clean up, kinda messy this :/
	 */
	static public function subject_prefix_template_hook(&$hook)
	{
		switch (sp_phpbb::$user->page['page_name'])
		{
			// Add the prefix dropdown to the posting page
			case 'posting.' . PHP_EXT :
				global $forum_id, $post_id, $topic_id;
				global $mode, $preview;

				// Must habs perms
				if (sp_phpbb::$auth->acl_get('!f_subject_prefix', $forum_id))
				{
					return;
				}

				$pid = request_var('subjectprefix', 0);

				// When editing we only pass this point when the *first* post is edited
				$selected = false;
				$sql = 'SELECT subject_prefix_id
					FROM ' . TOPICS_TABLE . "
					WHERE topic_id = $topic_id
						AND topic_first_post_id = $post_id";
				$result		= sp_phpbb::$db->sql_query($sql);
				$selected	= sp_phpbb::$db->sql_fetchfield('subject_prefix_id', false, $result);
				sp_phpbb::$db->sql_freeresult($result);

				// If submitted, change the selected prefix here
				if (isset($_POST['post']))
				{
					global $data;

					switch ($mode)
					{
						case 'edit' :
							if ($selected === false)
							{
								return;
							}

						// No Break;

						case 'post' :
							// Validate that this prefix can be used here
							$tree = $forums = array();
							sp_phpbb::$cache->obtain_prefix_forum_tree($tree, $forums);
							if (!isset($tree[$forum_id][$pid]) && $pid > 0)
							{
								trigger_error('PREFIX_NOT_ALLOWED');
							}

							// Only have to add the prefix
							$sql = 'UPDATE ' . TOPICS_TABLE . '
								SET subject_prefix_id = ' . $pid . '
								WHERE topic_id = ' . $data['topic_id'];
							sp_phpbb::$db->sql_query($sql);

							// Done :)
							return;
						break;
					}
				}
				// Display the dropbox
				else
				{
					// Set the correct prefix when previewing
					if (!empty($preview))
					{
						$selected = $pid;
					}

					switch ($mode)
					{
						case 'edit' :
							if ($selected === false)
							{
								// Nope
								return;
							}

						// No Break;

						case 'post';
							sp_phpbb::$template->assign_vars(array(
								'S_SUBJECT_PREFIX_OPTIONS'	=> sp_core::generate_prefix_options($forum_id, $selected),
							));
						break;
					}
				}
			break;
		}
	}
}

// Register
sp_hook::register($phpbb_hook);
