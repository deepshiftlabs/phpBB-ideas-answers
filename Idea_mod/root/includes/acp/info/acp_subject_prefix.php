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
 * Subject Prefix info class
 * @package acp
 */
class acp_subject_prefix_info
{
	/**
	 * Method returning all module info
	 * @return Array The module data
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_subject_prefix',
			'title'		=> 'ACP_SUBJECT_PREFIX',
			'version'	=> '1.2.0',
			'modes'		=> array(
				'main'	=> array('title' => 'ACP_SUBJECT_PREFIX', 'auth' => 'acl_a_subject_prefix', 'cat' => array('ACP_SUBJECT_PREFIX')),
				'add'	=> array('title' => 'ACP_SUBJECT_PREFIX_ADD', 'auth' => 'acl_a_subject_prefix_create', 'cat' => array('ACP_SUBJECT_PREFIX')),
//				'edit'	=> array('title' => 'ACP_SUBJECT_PREFIX_ADD', 'auth' => 'acl_a_subject_prefix_create', 'display' => false, 'cat' => array('ACP_SUBJECT_PREFIX')),
			),
		);
	}

	public function install()
	{
		// Not used
	}

	public function uninstall()
	{
		// Not used
	}
}
