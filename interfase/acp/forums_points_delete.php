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

$sql = 'SELECT *
	FROM _forum_topics_nopoints
	ORDER BY exclude_topic';
$result = sql_rowset($sql);

foreach ($result as $row) {
	$sql = 'UPDATE _forum_topics
		SET topic_points = 0
		WHERE topic_id = ?';
	sql_query(sql_filter($sql, $row['exclude_topic']));
	
	echo $row['exclude_topic'] . '<br />';
}

?>