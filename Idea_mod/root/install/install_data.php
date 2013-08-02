<?php
/**
 *
 * @package Ideas / Answers Installer
 * @copyright (c) 2011 Alexander Savchenko (saval@deepshiftlabs.com)
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

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Dev version, will be used as "main" schema
	'1.0.0-b1'	=> array(
		'table_add' => array(
			// The topic evaluation table
			array(TOPIC_EVALUATION_TABLE, array(
				'COLUMNS'	=> array(
					'topic_id'		=> array('UINT', 0),
					'evaluation'	=> array('TINT:1', 0),
					'user_id'	=> array('UINT', 0),
				),
				'PRIMARY_KEY' => array('topic_id', 'user_id'),
			)),
			// The main Subject Prefix table
			array(SUBJECT_PREFIX_TABLE, array(
				'COLUMNS'	=> array(
                  //`prefix_id` mediumint(8) unsigned NOT NULL auto_increment,
					'prefix_id'		=> array('UINT', NULL, 'auto_increment'),
					'prefix_title'	=> array('VCHAR:255', ''),
					'prefix_colour'	=> array('VCHAR:6', '000000'),
                    'prefix_bgcolour' => array('VCHAR:6', '000000'),
				),
				'PRIMARY_KEY' => 'prefix_id',
			)),

			// The prefix-forum table
			array(SUBJECT_PREFIX_FORUMS_TABLE, array(
				'COLUMNS'	=> array(
					'prefix_id'		=> array('UINT', 0),
					'forum_id'		=> array('UINT', 0),
					'prefix_order'	=> array('UINT', 0),
				),
				'KEYS'		=> array(
					'pid'	=> array('INDEX', array('prefix_id')),
					'fid'	=> array('INDEX', array('forum_id')),
				)
			)),
		),

		// Throw the permissions in tha mix
		'permission_add'	=> array(
            array('f_evaluation_see', false),
            array('f_evaluation', false),
            array('f_evaluation_own', false),
            array('f_evaluation_change', false),
            
			array('a_subject_prefix', true),		// Main admin permission
			array('a_subject_prefix_create', true),	// Can create/edit prefixes
			array('m_subject_prefix', false),		// MCP quick edit permission
			array('f_subject_prefix', false),		// Can post prefixes
		),

		'permission_set'	=> array(
			// Admin roles
			array('ROLE_ADMIN_STANDARD', array(
				'a_subject_prefix',
				'a_subject_prefix_create',
			)),
			array('ROLE_ADMIN_FORUM', array(
				'a_subject_prefix',
				'a_subject_prefix_create',
			)),
			array('ROLE_ADMIN_FULL', array(
				'a_subject_prefix',
				'a_subject_prefix_create',
			)),

			// Moderator roles
			array('ROLE_MOD_FULL', array(
				'm_subject_prefix',
			)),
			array('ROLE_MOD_STANDARD', array(
				'm_subject_prefix',
			)),

			// User roles
			array('ROLE_FORUM_FULL', array(
				'f_subject_prefix',
			)),
			array('ROLE_FORUM_STANDARD', array(
				'f_subject_prefix',
			)),
			array('ROLE_FORUM_POLLS', array(
				'f_subject_prefix',
			)),
		),

		// Add the modules
		'module_add' => array(
			// ACP Module
			array('acp', 'ACP_CAT_DOT_MODS', array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_langname'	=> 'ACP_SUBJECT_PREFIX',
				'module_auth'		=> 'acl_a_subject_prefix',
			)),
			array('acp', 'ACP_SUBJECT_PREFIX', array(
				'module_basename'	=> 'subject_prefix',
				'modes'				=> array('main', 'add'),
			)),

			// MCP Module
			array('mcp', 'MCP_MAIN', array(
				'module_basename'	=> 'subject_prefix',
				'modes'				=> array('quick_edit'),
			)),
		),

		// Alter the database structure
		'table_column_add' => array(
            array(FORUMS_TABLE, 'forum_evaluation', array('TINT:2', '0')),
			array(TOPICS_TABLE, 'subject_prefix_id', array('UINT', '0'))
		),

		'table_index_add' => array(
			array(TOPICS_TABLE, 'topic_first_post_id', 'topic_first_post_id'),
			array(TOPICS_TABLE, 'subject_prefix_id', 'subject_prefix_id'),
		),
            
        'table_row_insert' => array(
            array(STYLES_IMAGESET_DATA_TABLE, 
                array(
                    'image_name'  => 'ideas_read',
                    'image_filename'  => 'ideas_read.gif',
                    'image_height'  => '27',
                    'image_width'  => '27',
                    'imageset_id'  => $current_imageset_id
                )
            ),
            array(STYLES_IMAGESET_DATA_TABLE, 
                array(
                    'image_name'  => 'ideas_unread',
                    'image_filename'  => 'ideas_unread.gif',
                    'image_height'  => '27',
                    'image_width'  => '27',
                    'imageset_id'  => $current_imageset_id
                ),
            )
        ),
	),
	'1.0.0-rc1'	=> array(),	// No database changes
	'1.0.0'		=> array(),	// No database changes
	'1.0.1'		=> array(),	// No database changes
);
