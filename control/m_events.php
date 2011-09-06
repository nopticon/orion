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

class events extends common
{
	var $data = array();
	var $methods = array(
		'manage' => array('add', 'edit', 'delete'),
		'images' => array('add', 'edit', 'delete'),
		'messages' => array('edit', 'delete')
	);
	
	function events()
	{
		return;
	}
	
	function setup()
	{
		global $db;
		
		$event_id = $this->control->get_var('id', 0);
		if ($event_id)
		{
			$sql = 'SELECT *
				FROM _events
				WHERE id = ' . (int) $event_id;
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				$row['id'] = (int) $row['id'];
				$this->data = $row;
				
				$db->sql_freeresult($result);
				
				return true;
			}
		}
		
		return false;
	}
	
	function home()
	{
		global $user, $template;
		
		if ($this->setup())
		{
			$template->assign_block_vars('menu', array());
			foreach ($this->methods as $module => $void)
			{
				$template->assign_block_vars('menu.item', array(
					'URL' => s_link_control('a', array('a' => $this->data['subdomain'], 'mode' => $module)),
					'NAME' => $user->lang['CONTROL_A_' . strtoupper($module)])
				);
			}
			
//			$this->nav();
		}
	}
}

?>