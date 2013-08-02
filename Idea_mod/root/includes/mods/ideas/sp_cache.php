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

if (!class_exists('acm'))
{
	require PHPBB_ROOT_PATH . 'includes/acm/acm_' . $acm_type . '.' . PHP_EXT;
}

/**
 * Class that is used to handle all Subject Prefix related caching
 */
class sp_cache extends cache
{
	/**
	 * Fetch the Subject Prefixes from the database
	 * @return	Array	All Subject Prefixes
	 */
	public function obtain_subject_prefixes()
	{
		static $subject_prefixes = array();

		if (!empty($subject_prefixes))
		{
			return $subject_prefixes;
		}

		// In cache?
		if (($subject_prefixes = $this->get('_subject_prefixes')) === false)
		{
			$sql = 'SELECT *
				FROM ' . SUBJECT_PREFIX_TABLE;
			$result = sp_phpbb::$db->sql_query($sql);
			while ($prefix = sp_phpbb::$db->sql_fetchrow($result))
			{
				$subject_prefixes[$prefix['prefix_id']] = array(
					'id'		=> $prefix['prefix_id'],
					'title'		=> $prefix['prefix_title'],
					'colour'	=> $prefix['prefix_colour'],
                    'bgcolour'  => $prefix['prefix_bgcolour'],
				);
			}
			sp_phpbb::$db->sql_freeresult($result);
			$this->put('_subject_prefixes', $subject_prefixes);
		}

		return $subject_prefixes;
	}

	/**
	 * Obtain the "prefix forum tree", this is used to build the ACP page
	 * @param	array	$tree	The variable that will be filled with the prefix tree
	 * @param	array	$forums	The variable that will be filled with the forums data
	 * @return	void
	 */
	public function obtain_prefix_forum_tree(&$tree, &$forums)
	{
		static $_pft = array();

		// Only read from cache once
		if (empty($_pft['data']))
		{
			$_pft = $this->get('_prefix_forum_tree');
		}

		// Got data
		if ($_pft !== false)
		{
			$tree	= $_pft['tree'];
			$forums	= $_pft['forums'];
			return;
		}

		// Fetch from the cache
		$sql_ary = array(
			'SELECT'	=> 'f.forum_id, f.forum_name, sp.*, spt.prefix_order',
			'FROM'		=> array(
				SUBJECT_PREFIX_TABLE		=> 'sp',
				SUBJECT_PREFIX_FORUMS_TABLE	=> 'spt',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(
						FORUMS_TABLE	=> 'f',
					),
					'ON'	=> 'f.forum_id = spt.forum_id',
				),
			),
			'WHERE'		=> 'spt.prefix_id = sp.prefix_id',
			'ORDER_BY'	=> 'spt.prefix_order',
		);
		$result	= sp_phpbb::$db->sql_query(sp_phpbb::$db->sql_build_query('SELECT', $sql_ary));
		while ($row = sp_phpbb::$db->sql_fetchrow($result))
		{
			if (!isset($_pft['tree'][$row['forum_id']]))
			{
				$_pft['tree'][$row['forum_id']]		= array();
				$_pft['forums'][$row['forum_id']]	= $row['forum_name'];
			}

			$_pft['tree'][$row['forum_id']][$row['prefix_id']] = array(
				'prefix_id'		=> $row['prefix_id'],
				'prefix_title'	=> $row['prefix_title'],
				'prefix_colour'	=> $row['prefix_colour'],
				'prefix_order'	=> $row['prefix_order'],
			);
		}
		sp_phpbb::$db->sql_freeresult($result);

		// Cache
		$this->put('_prefix_forum_tree', $_pft);

		// Send back
		$tree	= $_pft['tree'];
		$forums	= $_pft['forums'];
	}

	/**
	 * One method all gone
	 */
	static public function subject_prefix_quick_clear()
	{
		sp_phpbb::$cache->destroy('_subject_prefixes');
		sp_phpbb::$cache->destroy('_prefix_forum_tree');
	}
}

// Drop the phpBB cache and overwrite it with the custom cache
sp_phpbb::$cache = null;
sp_phpbb::$cache = new sp_cache();
