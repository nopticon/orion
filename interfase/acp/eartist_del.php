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

_auth('founder');

if ($submit)
{
	$event = request_var('event', 0);
	$artist = request_var('artist', '', true);
	if (!$event || empty($artist))
	{
		_die();
	}
	
	$sql = 'SELECT *
		FROM _events
		WHERE id = ' . (int) $event;
	$result = $db->sql_query($sql);
	
	if (!$row = $db->sql_fetchrow($result))
	{
		_die();
	}
	$db->sql_freeresult($result);
	
	$e_artist = explode("\n", $artist);
	foreach ($e_artist as $row)
	{
		$subdomain = get_subdomain($row);
		
		$sql = "SELECT *
			FROM _artists
			WHERE subdomain = '" . $db->sql_escape($subdomain) . "'";
		$result = $db->sql_query($sql);
		
		if ($a_row = $db->sql_fetchrow($result))
		{
			$sql = 'DELETE FROM _artists_events
				WHERE a_artist = ' . (int) $a_row['ub'] . '
					AND a_event = ' . (int) $event;
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}
	
	echo 'Actualizado.<br /><br />';
}

?>

<form action="<?php echo $u; ?>" method="post">
<select name="event">
<?php

$sql = 'SELECT *
	FROM _events
	WHERE date > ' . time() . '
	ORDER BY date DESC';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	echo '<option value="' . $row['id'] . '">' . $row['title'] . ' - ' . $user->format_date($row['date']) . '</option>';
}
$db->sql_freeresult($result);

?>
</select>
<br />
<textarea name="artist" cols="50" rows="15"></textarea><br />
<input type="submit" name="submit" value="Siguiente" />
</form>