<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if (!defined('IN_NUCLEO'))
{
	die('Rock Republik &copy; 2006');
}

class community
{
	function team()
	{
		global $cache, $template;
		
		$team = array();
		$team_members = array();
		
		if (!$team = $cache->get('team'))
		{
			$sql = 'SELECT *
				FROM _team
				ORDER BY team_order';
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					$team[$row['team_id']] = $row;
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);
				
				$cache->save('team', $team);
			}
		}
		
		if (!$team_members = $cache->get('team_members'))
		{
			$sql = 'SELECT t.*
				FROM _team_members t, _members m
				WHERE t.member_id = m.user_id
				ORDER BY m.username';
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					$team_members[] = $row;
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);
				
				$cache->save('team_members', $team_members);
			}
		}
		
		//
		if (!sizeof($team) || !sizeof($team_members))
		{
			return;
		}
		
		include('./interfase/comments.php');
		$comments = new _comments();
		
		$sql_members = array();
		foreach ($team_members as $data)
		{
			$sql_members[] = $data['member_id'];
		}
		
		//
		$sql = 'SELECT user_id, username, username_base, user_color, user_avatar
			FROM _members
			WHERE user_id IN (' . implode(',', $sql_members) . ')
			ORDER BY user_id';
		$result = $db->sql_query($sql);
		
		$members_data = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$members_data[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);
		
		foreach ($team as $t_data)
		{
			if (!$t_data['team_show'])
			{
				continue;
			}
			
			$template->assign_block_vars('team', array(
				'TEAM_NAME' => $t_data['team_name'])
			);
			
			$tcol = 0;
			foreach ($team_members as $tm_data)
			{
				if ($t_data['team_id'] != $tm_data['team_id'])
				{
					continue;
				}
				
				if (!$tcol)
				{
					$template->assign_block_vars('team.row', array());
				}
				
				$up = $comments->user_profile($members_data[$tm_data['member_id']]);
				
				$template->assign_block_vars('team.row.member', array(
					'MOD' => ($tm_data['member_id'] == $t_data['team_mod']),
					'USERNAME' => $up['username'],
					'REALNAME' => $tm_data['real_name'],
					'PROFILE' => $up['profile'],
					'COLOR' => $up['user_color'],
					'AVATAR' => $up['user_avatar']
				));
				
				$tcol = ($tcol == 2) ? 0 : $tcol + 1;
			}
		}
		
		return;
	}
	
	function vars()
	{
		global $user, $config, $template;
		
		$template->assign_vars(array(
			'MEMBERS_COUNT' => $config['max_users'],
			'START_DATE' => $user->format_date($config['board_startdate'], "d " . $user->lang['OF_COMM'] . " F Y"))
		);
		
		return;
	}
	
	function online($sql, $block, $block_title, $unset_legend = false)
	{
		global $user, $template;
		static $user_bots;
		
		if (!isset($user_bots))
		{
			$bots = array();
			obtain_bots($bots);
			foreach ($bots as $row)
			{
				$user_bots[$row['user_id']] = true;
			}
		}
		
		foreach (array('last_user_id' => 0, 'users_visible' => 0, 'users_hidden' => 0, 'users_guests' => 0, 'users_bots' => 0, 'last_ip' => '', 'users_online' => 0) as $k => $v)
		{
			$$k = $v;
		}
		
		$template->assign_block_vars($block, array('L_TITLE' => $user->lang[$block_title]));
		$template->assign_block_vars($block . '.members', array());
		
		$result = $db->sql_query($sql);
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				if ($row['user_id'] != GUEST)
				{
					if ($row['user_id'] != $last_user_id)
					{
						$is_bot = isset($user_bots[$row['user_id']]);
						
						if (!$row['user_hideuser'])
						{
							$username = $row['username'];
							if ($is_bot)
							{
								$users_bots++;
							}
							else
							{
								$users_visible++;
							}
						}
						else
						{
							$username = '*' . $row['username'];
							$users_hidden++;
						}
						
						if (((!$row['user_hideuser'] || $user->data['is_founder']) && !$is_bot) || ($is_bot && $user->data['is_founder']))
						{
							$template->assign_block_vars($block . '.members.item', array(
								'USERNAME' => $username,
								'PROFILE' => s_link('m', $row['username_base']),
								'USER_COLOR' =>  $row['user_color'])
							);
						}
					}
					
					$last_user_id = $row['user_id'];
				}
				else
				{
					if ($row['session_ip'] != $last_ip)
					{
						$users_guests++;
					}
					
					$last_ip = $row['session_ip'];
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
		
		$users_total = (int) $users_visible + $users_hidden + $users_guests + $users_bots;
		
		if (!($users_visible + $users_hidden) || (!$users_visible && $users_hidden))
		{
			$template->assign_block_vars($block . '.members.none', array());
		}
		
		/*if (!$users_visible)
		{
			$template->assign_block_vars($block . '.members.none', array());
		}*/
		
		$template->assign_block_vars($block . '.legend', array());
		
		$online_ary = array(
			'MEMBERS_TOTAL' => $users_total,
			'MEMBERS_VISIBLE' => $users_visible,
			'MEMBERS_GUESTS' => $users_guests,
			'MEMBERS_HIDDEN' => $users_hidden,
			'MEMBERS_BOT' => $users_bots
		);
		if ($unset_legend !== false)
		{
			unset($online_ary[$unset_legend]);
		}
		foreach ($online_ary as $lk => $vk)
		{
			if (!$vk && $lk != 'MEMBERS_TOTAL')
			{
				continue;
			}
			$template->assign_block_vars($block . '.legend.item', array(
				'L_MEMBERS' => $user->lang[$lk . (($vk != 1) ? '2' : '')],
				'ONLINE_VALUE' => $vk)
			);
		}
	}
	
	function recent_members()
	{
		global $user, $template;
		
		$sql = 'SELECT username, username_base, user_color
			FROM _members
			WHERE user_type NOT IN (' . USER_INACTIVE . ', ' . USER_IGNORE . ')
			ORDER BY user_regdate DESC';
		$result = $db->sql_query_limit($sql, 5);
		
		$template->assign_block_vars('recent_members', array());
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('recent_members.item', array(
				'USERNAME' => $row['username'],
				'USER_COLOR' => $row['user_color'],
				'PROFILE' => s_link('m', $row['username_base']))
			);
		}
		$db->sql_freeresult($result);
	}
}

?>