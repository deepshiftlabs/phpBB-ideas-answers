<?php
/**
 *
 * @author Alexander Savchenko (saval@deepshiftlabs.com)
 *
 * @package phpBB3
 * @copyright (c) 2011 Alexander Savchenko
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Tables
define('TOPIC_EVALUATION_TABLE', $table_prefix . 'topics_evaluation');
define('SUBJECT_PREFIX_TABLE', $table_prefix . 'subject_prefixes');
define('SUBJECT_PREFIX_FORUMS_TABLE', $table_prefix . 'subject_prefix_forums');

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Ideas';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'subject_prefix_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/ideas/subject_prefix_common';

//set here your real value!
$current_imageset_id = 1;

// Get version info
include($phpbb_root_path . 'install/install_data.' . $phpEx);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

// clear cache
$umil->cache_purge(array(
	array(''),
	array('auth'),
	array('template'),
	array('theme'),
));