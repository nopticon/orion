<?php
/*
<NPT, a web development framework.>
Copyright (C) <2009>  <NPT>

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

require_once(ROOT . 'interfase/db.call.php');

class dcom {
	protected $connect;
	protected $result;
	protected $history;
	protected $row;
	protected $rowset;
	protected $queries;
	protected $noerror;
	
	protected $_access = array();
	
	final protected function access($d) {
		if ($d === false) {
			$d = decode_ht('.htda');
		}
		
		foreach (w('server login secret database') as $i => $k)
		{
			$this->_access[$k] = _decode($d[$i]);
		}
		unset($d);
		
		return;
	}
}

?>