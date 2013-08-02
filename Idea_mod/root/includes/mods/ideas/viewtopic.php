<?php
/**
*
* @package evaluation topics
* @version $Id: viewtopic.php , v 1.0.8 2010-02-13 Novan $
* @copyright (c) 2010 René Espenhahn (svrider.de)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function evaluation_display()
{
	global $template, $db, $user, $phpbb_root_path, $phpEx, $auth, $topic_data, $start;

    if(!isset($topic_data['forum_evaluation']) || 1!=$topic_data['forum_evaluation']) {
        return;
    }
	$user_id = $user->data['user_id'];

	if(($user_id != ANONYMOUS) && ($auth->acl_get('f_evaluation', $topic_data['forum_id'])))
	{
        //anzahl - evaluation_count, ergebnis - deleted
		$sql = 'SELECT e2.evaluation, SUM( CASE WHEN (e1.evaluation >0) THEN e1.evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE e1.evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT. ' END ) AS evaluation_count
				FROM ' . TOPICS_EVALUATION_TABLE . ' e1
				LEFT JOIN ' . TOPICS_EVALUATION_TABLE . ' e2
				ON ((e2.topic_id = e1.topic_id) AND (e2.user_id = ' . $user_id . '))
				WHERE e1.topic_id = ' . $topic_data['topic_id'] . '
				GROUP BY e1.topic_id';
	}
	else
	{
		$sql = 'SELECT SUM( CASE WHEN (evaluation >0) THEN evaluation * '.TOPICS_EVALUATION_POS_VOICE_WEIGHT.' ELSE evaluation * '.TOPICS_EVALUATION_NEG_VOICE_WEIGHT.' END ) AS evaluation_count FROM ' . TOPICS_EVALUATION_TABLE . ' WHERE topic_id = ' . $topic_data['topic_id'];
	}
	$evaluation = $db->sql_fetchrow($db->sql_query($sql));

	$out        = '';
	$eval = $can_vote = false;

	if($user_id != ANONYMOUS)
	{
		$eval_own    = $auth->acl_get('f_evaluation_own',    $topic_data['forum_id']);
		$eval_change = $auth->acl_get('f_evaluation_change', $topic_data['forum_id']);
		$eval        = $auth->acl_get('f_evaluation',        $topic_data['forum_id']);

		if(($eval) && (($topic_data['topic_poster'] != $user_id) || ($eval_own)) && ((!$evaluation['evaluation']) || ($eval_change)))
		{
            $can_vote = true;
            $vote = request_var('evaluation', 0);
			if($vote && $vote != $evaluation['evaluation'])//there is should be check on prev eval direction, only opposite allowed!!!!!!
			{
				if(!is_null($evaluation['evaluation']))   //exists own previous voiting
				{
					$sql = 'UPDATE ' . TOPICS_EVALUATION_TABLE . ' SET
								evaluation = "' . $vote . '"
							WHERE topic_id = ' . $topic_data['topic_id'] . '
							AND user_id = ' . $user->data['user_id'];

					$db->sql_query($sql);
				}
				elseif(!$check)
				{
					$sql = 'INSERT INTO ' . TOPICS_EVALUATION_TABLE . ' (topic_id, evaluation, user_id) VALUES ("' . $topic_data['topic_id'] . '", "' . $vote . '", "' . $user->data['user_id'] . '")';
					$db->sql_query($sql);
				}
				redirect(append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, 'f=' . $topic_data['forum_id'] . '&amp;t=' . $topic_data['topic_id'] . '&amp;start=' . request_var('start', 0)));
			}
		}
	}
	$user->add_lang('mods/evaluation');
    $template->assign_vars(array(
				'EVALUATION_RATE_LNG'		=> $user->lang['TOPIC_EVALUATION_RESULT_LBL'],
				'EVALUATION_CNT'		    => (isset($evaluation['evaluation_count']) ? $evaluation['evaluation_count'] : 0),
				'EVALUATION_LINK'			=> append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, 'f=' . $topic_data['forum_id'] . '&t=' . $topic_data['topic_id'] . '&start=' . intval($start)))
			); 

    //There is we draw block with buttons (if topic can be voited), just topic rate if evaluation can be shown but we can`t voite or empty string 
	if($auth->acl_get('f_evaluation_see', $topic_data['forum_id'])) 
	{
		if(isset($evaluation['evaluation']) && $evaluation['evaluation'] == 1) // Has he already evaluated and evaluated with positive rate?
		{
			$own_evaluation = $user->lang['YOUR_EVALUATION1'];
            $template->assign_var('EVALUATION_CAN_VOTE_NEG', true);
		}
		elseif(isset($evaluation['evaluation']) && $evaluation['evaluation'] == -1) // Has he already evaluated and evaluated with negative rate?
		{
			$own_evaluation = $user->lang['YOUR_EVALUATION2'];
            $template->assign_var('EVALUATION_CAN_VOTE_POS', true);
		}
		else
		{
			$own_evaluation = '';
            $template->assign_var('EVALUATION_CAN_VOTE_POS', true);//not evaluated yet, so you can vote in any hand
            $template->assign_var('EVALUATION_CAN_VOTE_NEG', true);
		}
		$title = sprintf($user->lang['TOPIC_EVALUATION_RESULT'], $evaluation['evaluation_count'], $own_evaluation);
        $template->assign_var('EVALUATION_TITLE', $title);
        
        if($can_vote) {
            $template->assign_var('EVALUATION_CAN_VOTE', true); //Here block with voiting buttons
        }
        $template->assign_var('EVALUATION_HTML', true);
	}
}
?>