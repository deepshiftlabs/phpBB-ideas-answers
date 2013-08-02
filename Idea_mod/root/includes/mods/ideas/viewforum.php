<?php
/**
*
* @package evaluation topics
* @version $Id: viewforum.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function evaluation_get_topic_data()
{
	global $user, $row, $forum_data;

	$res = array('EVALUATION_RATE_LNG' => $user->lang['TOPIC_EVALUATION_RESULT_LBL']);
	if(1== $forum_data['forum_evaluation'] && array_key_exists('evaluation_count', $row) && intval($row['evaluation_count']) != 0)
	{
		if(isset($row['own_evaluation']) && $row['own_evaluation'] == 1) // Has he already evaluated and evaluated with positive rate?
		{
			$own_evaluation = $user->lang['YOUR_EVALUATION1'];
		}
		elseif(isset($row['own_evaluation']) && $row['own_evaluation'] == -1) // Has he already evaluated and evaluated with negative rate?
		{
            $own_evaluation = $user->lang['YOUR_EVALUATION2'];
		}
		else
		{
			$own_evaluation = '';
		}

        $res['EVALUATION_TITLE'] = sprintf($user->lang['TOPIC_EVALUATION_RESULT'], $row['evaluation_count'], $own_evaluation);
        $res['EVALUATION_CNT'] = $row['evaluation_count'];

	}  else if(1== $forum_data['forum_evaluation'] && array_key_exists('evaluation_count', $row)) {
        $res['EVALUATION_TITLE'] = $user->lang['TOPIC_NOT_EVALUATED'];
        $res['EVALUATION_CNT'] = 0;
    }
	return $res;
}

function evaluation_extend_sql()
{
	global $auth, $template, $user, $sql_array, $forum_id, $forum_data;

	if(1== $forum_data['forum_evaluation'] && $auth->acl_get('f_evaluation_see', $forum_id))
	{
		if($user->data['user_id'] != ANONYMOUS)
		{
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_EVALUATION_TABLE => 'te'),  'ON' => 'te.topic_id = t.topic_id');
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_EVALUATION_TABLE => 'te2'), 'ON' => '((te2.topic_id = t.topic_id) AND (te2.user_id = ' . $user->data['user_id'] . '))');
			//$sql_array['SELECT']	 .= ', te2.evaluation AS own_evaluation, ROUND(AVG(te.evaluation), 2) AS evaluation, COUNT(te.evaluation) AS evaluation_count';
            $sql_array['SELECT']	 .= ', te2.evaluation AS own_evaluation, SUM( CASE WHEN (te.evaluation >0) THEN te.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE te.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT. ' END ) AS evaluation_count';
		}
		else
		{
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_EVALUATION_TABLE => 'te'),  'ON' => 'te.topic_id = t.topic_id');
			//$sql_array['SELECT']	 .= ', ROUND(AVG(te.evaluation), 2) AS evaluation, COUNT(te.evaluation) AS evaluation_count';
            $sql_array['SELECT']	 .= ', SUM( CASE WHEN (te.evaluation >0) THEN te.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE te.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT.' END ) AS evaluation_count';
		}
		$user->add_lang('mods/evaluation');
	}
}

function evaluation_extend_sql_group()
{
	global $auth, $forum_id, $forum_data;

	if(1== $forum_data['forum_evaluation'] && $auth->acl_get('f_evaluation_see', $forum_id))
	{
		return 't.topic_id';
	}
	return '';
}

function evaluation_extend_sql_orderby()
{
    global $forum_data, $sql_sort_order, $store_reverse;

    if(1 == $forum_data['forum_evaluation'])
    {
        $store_reverse = false;
        $sql_sort_order = 'evaluation_count DESC';
    }
}

function evaluation_prepare_viewforum_topics_sql()
{
    global $sql_where, $sql_approved, $sql_limit_time, $store_reverse, $sql_sort_order;

    $sql = 'SELECT t.topic_id, SUM( CASE WHEN (te.evaluation >0) THEN te.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE te.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT.' END ) AS evaluation_count
    	FROM ' . TOPICS_TABLE . " t
        LEFT JOIN " . TOPICS_EVALUATION_TABLE ." te ON te.topic_id = t.topic_id
    	WHERE $sql_where
    		AND t.topic_type IN (" . POST_NORMAL . ', ' . POST_STICKY . ")
    		$sql_approved
    		$sql_limit_time
        GROUP BY t.topic_id
    	ORDER BY t.topic_type " . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order;
    return $sql;
}
?>