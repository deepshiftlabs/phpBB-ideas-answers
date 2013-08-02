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
* @package module_install
*/
class mcp_subject_prefix_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_subject_prefix',
			'title'		=> 'MCP_SUBJECT_PREFIX',
			'version'	=> '1.2.0',
			'modes'		=> array(
				'quick_edit' => array('title' => 'MCP_SUBJECT_PREFIX', 'auth' => 'acl_m_subject_prefix,$id',  'display' => false, 'cat' => array('MCP_MAIN')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}