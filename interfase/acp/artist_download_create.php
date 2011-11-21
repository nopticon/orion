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

class artist_download_create extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function home() {
		global $config, $user, $template;
		
		$limit = set_time_limit(0);
		$error = array();
		
		if ($this->submit) {
			require_once(ROOT . 'interfase/upload.php');
			$upload = new upload();
			
			$a_id = request_var('artist', 0);
			
			$sql = 'SELECT ub, subdomain
				FROM _artists
				WHERE ub = ?';
			if (!$artist_data = sql_fieldrow(sql_filter($sql, $a_id))) {
				fatal_error();
			}
			
			$filepath = artist_path($artist_data['subdomain'], $artist_data['ub'], true, true);
			$filepath_1 = $filepath . 'media/';
			
			$f = $upload->process($filepath_1, $_FILES['add_dl'], w('mp3'), upload_maxsize());
			
			if (!sizeof($upload->error) && $f !== false) {
				require_once(ROOT . 'interfase/id3/getid3/getid3.php');
				$getID3 = new getID3;
				
				$sql = 'SELECT MAX(id) AS total
					FROM _dl';
				$a = sql_field($sql, 'total', 0);
				
				$proc = 0;
				foreach ($f as $row)
				{
					$a++;
					$proc++;
					
					$filename = $upload->rename($row, $a);
					$tags = $getID3->analyze($filename);
					
					$clean = array('title', 'genre', 'album', 'year');
					foreach ($clean as $i) {
						${'clean_' . $i} = (isset($tags['tags']['id3v1'][$i][0])) ? htmlencode($tags['tags']['id3v1'][$i][0]) : '';
					}
					
					$clean_album = ($clean_album != '') ? $clean_album : 'Single';
					$clean_genre = ($clean_genre != '') ? $clean_genre : '-';
					$clean_year = ($clean_year != '') ? $clean_year : '-';
					
					$insert_dl = array(
						'ud' => 1,
						'ub' => $a_id,
						'title' => $clean_title,
						'views' => 0,
						'downloads' => 0,
						'votes' => 0,
						'posts' => 0,
						'date' => time(),
						'filesize' => @filesize($filename),
						'duration' => $tags['playtime_string'],
						'genre' => $clean_genre,
						'album' => $clean_album,
						'year' => $clean_year
					);
					$sql = 'INSERT INTO _dl' . sql_build('INSERT', $insert_dl);
					$dl_id = sql_query_nextid();
				}
				
				$sql = 'UPDATE _artists SET um = um + ??
					WHERE ub = ?';
				sql_query(sql_filter($sql, $proc, $a_id));
				
				$cache->delete('downloads_list');
				redirect(s_link('topic', $topic_id));
			} else {
				$template->assign_block_vars('error', array(
					'MESSAGE' => parse_error($upload->error))
				);
			}
		}
		
		$sql = 'SELECT *
			FROM _artists
			ORDER BY name';
		$result = sql_rowset($sql);
		
		foreach ($result as $i => $row) {
			if (!$i) {
				$template->assign_block_vars('artists', array());
			}
			
			$template->assign_block_vars('artists.row', array(
				'ARTIST_ID' => $row['ub'],
				'ARTIST_NAME' => $row['name'])
			);
		}
		
		$template_vars = array(
			'S_UPLOAD_ACTION' => $u,
			'MAX_FILESIZE' => upload_maxsize(),
			'MAX_FILESIZE2' => (upload_maxsize() / 1024 / 1024)
		);
		
		page_layout('DL', 'acp/a_download', $template_vars, false);
	}
}

?>