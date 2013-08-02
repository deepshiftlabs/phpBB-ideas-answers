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
 * Subject Prefix module class
 * @package acp
 */
class acp_subject_prefix
{
	public $u_action;

	private $error = array();

	public function main($id, $mode)
	{
		global $phpbb_admin_path;	// Anoyingly can't use a constant due to phpBB :/

		// Set some stuff we *really* need
		$this->tpl_name = 'acp_subject_prefix';
		$this->page_title = 'ACP_SUBJECT_PREFIX';
		add_form_key('acp_subject_prefix');
		$action	= request_var('action', '');

		switch ($mode)
		{
			case 'add'  :
				$pid = request_var('pid', 0);

				// Handle
				if (isset($_POST['submit']))
				{
					// Get the data
					$colour		= request_var('prefix_colour', '');
                    $bgcolour	= request_var('prefix_bgcolour', '');
					$forum_ids	= request_var('prefix_forums_id', array(0 => 0));
					$title		= request_var('prefix_title', '', true);

					// Create or edit
					if ($pid > 0)
					{
						sp_core::prefix_update($pid, $title, $colour, $bgcolour, $forum_ids, $this->error);
						$msg = 'PREFIX_SUCCESSFULLY_EDITED';
					}
					else
					{
						$pid = sp_core::prefix_add($title, $colour, $bgcolour, $forum_ids, $this->error);
						$msg = 'PREFIX_SUCCESSFULLY_ADDED';
					}

					// Redirect
					meta_refresh(5, $this->u_action);
					trigger_error(sp_phpbb::$user->lang[$msg]. adm_back_link($this->u_action));
				}

				// Editing
				if ($pid > 0)
				{
					$prefixes = sp_phpbb::$cache->obtain_subject_prefixes();
					if (!empty($prefixes[$pid]))
					{
						sp_phpbb::$template->assign_vars(array(
							'COLOUR'	=> $prefixes[$pid]['colour'],
                            'BGCOLOUR'  => $prefixes[$pid]['bgcolour'],
							'TITLE'		=> $prefixes[$pid]['title'],
						));

						// The forum select
						$selected = array();
						$sql = 'SELECT forum_id
							FROM ' . SUBJECT_PREFIX_FORUMS_TABLE . '
							WHERE prefix_id = ' . $pid;
						$result = sp_phpbb::$db->sql_query($sql);
						while ($row = sp_phpbb::$db->sql_fetchrow($result))
						{
							$selected[] = $row['forum_id'];
						}
						sp_phpbb::$db->sql_freeresult($result);

						$forum_select = make_forum_select($selected, false, true, false, false);
					}
				}

				if (!isset($forum_select))
				{
					$forum_select = make_forum_select(false, false, true, false, false);
				}

				// Display page
				sp_phpbb::$template->assign_vars(array(
					'L_SUBJECT_PREFIX_ADD_EDIT'	=> sp_phpbb::$user->lang('SUBJECT_PREFIX_ADD_EDIT', ($mode == 'add') ? 0 : 1),
					'PREFIX_FORUMS_OPTIONS'		=> $forum_select,
					'S_EDIT'					=> true,
					'U_ACTION'					=> $this->u_action . '&amp;pid=' . $pid,
					'U_SWATCH'					=> append_sid($phpbb_admin_path . 'swatch.' . PHP_EXT, array('form' => 'acp_subject_prefix', 'name' => 'prefix_colour')),
                    'U_BGSWATCH'				=> append_sid($phpbb_admin_path . 'swatch.' . PHP_EXT, array('form' => 'acp_subject_prefix', 'name' => 'prefix_bgcolour')),
				));
			break;

			case 'main' :
				// Quick actions
				if (method_exists($this, 'qa_' . $action))
				{
					call_user_func(array($this, 'qa_' . $action));
				}

				$data = $forums = array();
				sp_phpbb::$cache->obtain_prefix_forum_tree($data, $forums);

				if (is_array($data) && is_array($forums))
				{
					ksort($data);

					// Output the list
					foreach ($data as $forum_id => $prefixes)
					{
						// The forum block
						sp_phpbb::$template->assign_block_vars('forumrow', array(
							'FORUMNAME'	=> $forums[$forum_id],
							'FORUM_ID'	=> $forum_id,
						));

						// The prefixes
						foreach ($prefixes as $prefix)
						{
							sp_phpbb::$template->assign_block_vars('forumrow.prefixrow', array(
								'PREFIX_ID'		=> $prefix['prefix_id'],
								'PREFIX_NAME'	=> $prefix['prefix_title'],
								'PREFIX_COLOUR'	=> $prefix['prefix_colour'],
                                'PREFIX_BGCOLOUR'	=> $prefix['prefix_bgcolour'],
								'PREFIX_FULL'	=> sp_core::generate_prefix_string($prefix['prefix_id']),

								// Actions
								'U_DELETE'		=> (sp_phpbb::$auth->acl_get('a_subject_prefix_create')) ? $this->u_action . "&amp;action=delete&amp;pid={$prefix['prefix_id']}&amp;fid={$forum_id}" : false,
								'U_EDIT'		=> (sp_phpbb::$auth->acl_get('a_subject_prefix_create')) ? $this->u_action . "&amp;mode=add&amp;pid={$prefix['prefix_id']}" : false,
								'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move&amp;direction=down&amp;prefix_order=' . $prefix['prefix_order'] . '&amp;f=' . $forum_id,
								'U_MOVE_UP'		=> $this->u_action . '&amp;action=move&amp;direction=up&amp;prefix_order=' . $prefix['prefix_order'] . '&amp;f=' . $forum_id,
							));
						}
					}
				}

				// Some common stuff
				sp_phpbb::$template->assign_vars(array(
					'U_SUBJECT_PREFIX_AJAX_REQUEST' => append_sid(PHPBB_ROOT_PATH . 'sp_ajax.' . PHP_EXT),
				));
			break;
		}
	}

	/**
	 * Delete a prefix
	 * @return void
	 */
	private function qa_delete()
	{
		$fid = request_var('fid', 0);
		$pid = request_var('pid', 0);
		sp_core::prefix_delete_forum($pid, $fid);
	}

	/**
	 * Reorder the prefixes
	 * @return void
	 */
	private function qa_move()
	{
		$direction	= ($_GET['direction'] == 'down') ? 'down' : 'up';
		$fid		= request_var('f', 0);
		$order		= request_var('prefix_order', 0);
		sp_core::prefix_reorder($fid, $order, $direction);
	}
}
