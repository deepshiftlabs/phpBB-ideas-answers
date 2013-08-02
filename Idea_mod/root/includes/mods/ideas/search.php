<?php
/**
*
* @package evaluation topics
* @version $Id: search.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 Ren Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function evaluation_extend_sql(&$sql, $show_results)
{
	global $user;

	if($user->data['user_id'] != ANONYMOUS)
	{
		$sql  = str_replace('SELECT', 'SELECT f.forum_evaluation, e2.evaluation AS own_evaluation, SUM( CASE WHEN (e.evaluation >0) THEN e.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE e.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT. ' END ) AS evaluation_count,', $sql);
		$sql  = str_replace('WHERE',  'LEFT JOIN ' . TOPICS_EVALUATION_TABLE . ' e ON (e.topic_id = t.topic_id) LEFT JOIN ' . TOPICS_EVALUATION_TABLE . ' e2 ON (e2.topic_id = t.topic_id AND e2.user_id = ' . $user->data['user_id'] . ') WHERE', $sql);
	}
	else
	{
		$sql  = str_replace('SELECT', 'SELECT f.forum_evaluation, SUM( CASE WHEN (e.evaluation >0) THEN e.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE e.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT. ' END ) AS evaluation_count,', $sql);
		$sql  = str_replace('WHERE',  'LEFT JOIN ' . TOPICS_EVALUATION_TABLE . ' e ON (e.topic_id = t.topic_id) WHERE', $sql);
	}
	if($show_results == 'posts')
	{
		$sql = str_replace('ORDER BY', 'GROUP BY p.post_id ORDER BY', $sql);
	}
	else
	{
		$sql = str_replace('ORDER BY', 'GROUP BY t.topic_id ORDER BY', $sql);
	}
}

function evaluation_extend_template(&$tpl_ary, $row)
{
	global $auth, $user;
	//static $lang = false;

	$out = '';

	if(1 == $row['forum_evaluation'] && $auth->acl_get('f_evaluation_see', $row['forum_id']))
	{
		//if(!$lang)
		//{
			//$lang = true;
			$user->add_lang('mods/evaluation');
		//}
		if(isset($row['own_evaluation']) && $row['own_evaluation'] == 1) // Has he already evaluated and evaluated with one star?
		{
			$own_evaluation = $user->lang['YOUR_EVALUATION1'];
		}
		elseif(isset($row['own_evaluation']) && $row['own_evaluation'] == -1) // Has he already evaluated and evaluated with two or more stars?
		{
			$own_evaluation = $user->lang['YOUR_EVALUATION2'];
		}
		else
		{
			$own_evaluation = '';
		}
		//$alt = sprintf($user->lang['TOPIC_EVALUATION_RESULT'], $row['evaluation_count'], $row['evaluation'], $own_evaluation);
        $title = sprintf($user->lang['TOPIC_EVALUATION_RESULT'], $row['evaluation_count'], $own_evaluation);
        //$out = sprintf('<span class="eval_rate">%s</span><span title="%s">%d</span>', $user->lang['TOPIC_EVALUATION_RESULT_LBL'], $title, $row['evaluation_count']);

	    $tpl_ary += array(
                        'EVALUATION_CNT' => ($row['evaluation_count'] ? $row['evaluation_count'] : 0),
                        'EVALUATION_RATE_LNG' => $user->lang['TOPIC_EVALUATION_RESULT_LBL'],
                        'EVALUATION_TITLE' => ($row['evaluation_count'] ? $title : $user->lang['TOPIC_NOT_EVALUATED'])
                    );
	}
}
?>