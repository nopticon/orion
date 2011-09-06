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
if (!defined('IN_NUCLEO')) exit;

class board
{
	var $cat_data = array();
	var $forum_data = array();
	var $msg;
	
	function categories()
	{
		global $cache;
		
		if (!$this->cat_data = $cache->get('forum_categories'))
		{
			global $db;
			
			$sql = 'SELECT cat_id, cat_title
				FROM _forum_categories
				ORDER BY cat_order';
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					$this->cat_data[] = $row;
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);
				
				$cache->save('forum_categories', $this->cat_data);
			}
		}
		
		if (!sizeof($this->cat_data))
		{
			return false;
		}
		
		return true;
	}
	
	function forums()
	{
		global $db;
		
		$sql = 'SELECT f.*, t.topic_id, t.topic_title, p.post_id, p.post_time, p.post_username, u.user_id, u.username, u.username_base, u.user_color 
			FROM (( _forums f
			LEFT JOIN _forum_topics t ON t.topic_id = f.forum_last_topic_id
			LEFT JOIN _forum_posts p ON p.post_id = t.topic_last_post_id)
			LEFT JOIN _members u ON u.user_id = p.poster_id)
			ORDER BY f.cat_id, f.forum_order';
		$result = $db->sql_query($sql);
		
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$this->forum_data[] = $row;
			}
			while ($row = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);
		}
		
		if (!sizeof($this->forum_data))
		{
			return false;
		}
		
		return true;
	}
	
	function index()
	{
		global $user, $auth, $template;
		
		$is_auth_ary = array();
		$is_auth_ary = $auth->forum(AUTH_VIEW, AUTH_LIST_ALL, $this->forum_data);
		
		foreach ($this->cat_data as $c_data)
		{
			$no_catdata = false;
			
			foreach ($this->forum_data as $f_data)
			{
				if ($f_data['cat_id'] == $c_data['cat_id'])
				{
					if (!$is_auth_ary[$f_data['forum_id']]['auth_view'])
					{
						continue;
					}

					if ($user->data['user_id'] == 5777 && $f_data['forum_name'] == '[root]')
					{
						continue;
					}
					
					if ($f_data['post_id'])
					{
						$f_data['topic_title'] = (strlen($f_data['topic_title']) > 30) ? substr($f_data['topic_title'], 0, 30) . '...' : $f_data['topic_title'];
						
						$last_topic = '<a class="bold" href="' . s_link('topic', $f_data['topic_id']) . '">' . $f_data['topic_title'] . '</a>';
						$last_poster = ($f_data['user_id'] == GUEST) ? '<span style="color:#' . $f_data['user_color'] . '; font-weight: bold">*' . (($f_data['post_username'] != '') ? $f_data['post_username'] : $user->lang['GUEST']) . '</span>' : '<a style="color:#' . $f_data['user_color'] . '; font-weight: bold" href="' . s_link('m', $f_data['username_base']) . '">' . $f_data['username'] . '</a>';
						$last_post_time = '<a href="' . s_link('post', $f_data['post_id']) . '#' . $f_data['post_id'] . '">' . $user->format_date($f_data['post_time']) . '</a>';
					}
					else
					{
						$last_poster = $last_post_time = $last_topic = '';
					}
					
					if (!$no_catdata)
					{
						$template->assign_block_vars('category', array(
							'DESCRIPTION' => $c_data['cat_title'])
						);
						$no_catdata = true;
					}
		
					$template->assign_block_vars('category.forums',	array(
						'FORUM_NAME' => $f_data['forum_name'],
						'FORUM_DESC' => $f_data['forum_desc'],
						'POSTS' => $f_data['forum_posts'],
						'TOPICS' => $f_data['forum_topics'],
						'LAST_TOPIC' => $last_topic,
						'LAST_POSTER' => $last_poster,
						'LAST_POST_TIME' => $last_post_time,
						
						'U_FORUM' => s_link('forum', $f_data['forum_alias']))
					);
				}
			}
		}
	}
	
	function birthdays()
	{
		global $template;
		
		$sql = "SELECT user_id, username, username_base, user_color, user_avatar, user_posts
			FROM _members
			WHERE user_birthday LIKE '%" . date('md') . "'
				AND user_type NOT IN (" . USER_INACTIVE . ", " . USER_IGNORE . ")
				/*AND user_lastvisit > 1167631200*/
			ORDER BY user_posts DESC, username";
		$result = $db->sql_query($sql);
		
		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('top_posters', array());
			
			do
			{
				$profile = $this->msg->user_profile($row);
				
				$template->assign_block_vars('top_posters.item', array(
					'USERNAME' => $profile['username'],
					'PROFILE' => $profile['profile'],
					'COLOR' => $profile['user_color'],
					'AVATAR' => $profile['user_avatar'],
					'POSTS' => $profile['user_posts'])
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
		
		return;
	}
	
	function top_posters()
	{
		global $template;
		
		$sql = 'SELECT user_id, username, username_base, user_color, user_avatar, user_posts
			FROM _members
			WHERE user_id <> ' . GUEST . '
			ORDER BY user_posts DESC
			LIMIT 8';
		$result = $db->sql_query($sql);
		
		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('top_posters', array());
			
			do
			{
				$profile = $this->msg->user_profile($row);
				
				$template->assign_block_vars('top_posters.item', array(
					'USERNAME' => $profile['username'],
					'PROFILE' => $profile['profile'],
					'COLOR' => $profile['user_color'],
					'AVATAR' => $profile['user_avatar'],
					'POSTS' => $profile['user_posts'])
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}
}

?>