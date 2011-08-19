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
	exit();
}

_auth('founder');

if ($submit)
{
	$username = request_var('username', '');
	$username = get_username_base($username);
	
	$sql = "SELECT *
		FROM _members
		WHERE username_base = '" . $db->sql_escape($username) . "'";
	$result = $db->sql_query($sql);
	
	if (!$userdata = $db->sql_fetchrow($result))
	{
		fatal_error();
	}
	$db->sql_freeresult($result);
	
	$sql = array(
		'DELETE FROM _members WHERE user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _banlist WHERE ban_userid = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_ban WHERE user_id = ' . (int) $userdata['user_id'] . ' OR banned_user = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_friends WHERE user_id = ' . (int) $userdata['user_id'] . ' OR buddy_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_group WHERE user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_iplog WHERE log_user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_ref_assoc WHERE ref_uid = ' . (int) $userdata['user_id'] . ' OR ref_orig = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_ref_invite WHERE invite_uid = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_unread WHERE user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _members_viewers WHERE viewer_id = ' . (int) $userdata['user_id'] . ' OR user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _poll_voters WHERE vote_user_id = ' . (int) $userdata['user_id'],
		'UPDATE _members_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		'UPDATE _news_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		
		'UPDATE _artists_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _artists_auth WHERE user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _artists_viewers WHERE user_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _artists_voters WHERE user_id = ' . (int) $userdata['user_id'],
		'UPDATE _dl_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		'DELETE FROM _dl_voters WHERE user_id = ' . (int) $userdata['user_id'],
		'UPDATE _events_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		'UPDATE _forum_posts SET poster_id = 1 WHERE poster_id = ' . (int) $userdata['user_id'],
		'UPDATE _forum_topics SET topic_poster = 1 WHERE topic_poster = ' . (int) $userdata['user_id']
	);
	$db->sql_query($sql);
	/*echo '<pre>';
	print_r($sql);
	echo '</pre>';*/
	
	_die('El registro de <strong>' . $userdata['username'] . '</strong> fue eliminado.');
	
}

?>
<html>
<head>
<title>Delete users</title>
</head>

<body>
<form action="<?php echo $u; ?>" method="post">
Nombre de usuario: <input type="text" name="username" size="100" />
<input type="submit" name="submit" value="Consultar" />
</form>
</body>
</html>