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
 * A wrapper class for the most common phpBB stuff
 */
abstract class sp_phpbb
{
	/**@#+
	 * @var mixed all the phpBB common used objects
	 */
	static public $auth		= null;
	static public $cache	= null;
	static public $config	= array();
	static public $db		= null;
	static public $template	= null;
	static public $user		= null;
	/**@#-*/

	/**
	 * Initialise this object
	 * @return void
	 */
	static public function init()
	{
		// Run only once
		if (self::$cache instanceof acm)
		{
			return;
		}

		// Set the phpBB objects
		global $auth, $config, $cache, $db, $template, $user;
		self::$auth		= &$auth;
		self::$cache	= &$cache;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;

		// Create constants out of $phpbb_root_path and $phpEx
		global $phpbb_root_path, $phpEx;
		if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', $phpbb_root_path);
		if (!defined('PHP_EXT')) define('PHP_EXT', $phpEx);
	}
}
