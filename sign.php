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
define('IN_NUCLEO', true);
require_once('./interfase/common.php');

$user->init(false);

$action = request_var('mode', '');

$v_fields = array();
$fields = array(
	'username' => '',
	'email' => '',
	'key' => '',
	'key_confirm' => '',
	'gender' => 0,
	'birthday_month' => 0,
	'birthday_day' => 0,
	'birthday_year' => 0,
	'tos' => 0,
	'ref' => 0
);

foreach ($fields as $k => $v) {
	$v_fields[$k] = $v;
}

$code_invite = request_var('invite', '');

if (!empty($code_invite)) {
	$sql = 'SELECT i.invite_email, m.user_email
		FROM _members_ref_invite i, _members m
		WHERE i.invite_code = ?
			AND i.invite_uid = m.user_id';
	if (!$code_invite_row = sql_fieldrow(sql_filter($sql, $code_invite))) {
		fatal_error();
	}
	
	$v_fields['refop'] = 1;
	$v_fields['refby'] = $code_invite_row['user_email'];
	$v_fields['email'] = $code_invite_row['invite_email'];
	unset($code_invite_row);
}

switch ($action) {
	case 'in':
		if ($user->data['is_member'] && !isset($_POST['admin'])) {
			redirect(s_link());
		}
		
		if (isset($_POST['login']) && (!$user->data['is_member'] || isset($_POST['admin']))) {
			$username = phpbb_clean_username(request_var('username', ''));
			$password = request_var('password', '');
			$ref = request_var('ref', '');
			
			$adm = (isset($_POST['admin'])) ? 1 : 0;
			
			if (!empty($username) && !empty($password)) {
				$sql = 'SELECT user_id, username, user_password, user_type, user_return_unread, user_country, user_avatar, user_location, user_gender, user_birthday
					FROM _members
					WHERE username = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $username))) {
					if ((user_password($password) == $row['user_password']) && ($row['user_type'] != USER_INACTIVE && $row['user_type'] != USER_IGNORE)) {
						$user->session_create($row['user_id'], $adm);
						
						$ref = ($ref == '' || ($row['user_return_unread'] && preg_match('#' . $config['server_name'] . '/$#', $ref))) ? s_link('new') : $ref;
						
						if (!$row['user_country'] || !$row['user_location'] || !$row['user_gender'] || !$row['user_birthday'] || !$row['user_avatar']) {
							$ref = s_link('my', 'profile');
						}
						redirect($ref);
					}
				}
			}
			
			do_login('', $adm);
		}
		break;
	case 'out':
		if ($user->data['is_member']) {
			$user->session_kill();
		}
		
		redirect(s_link());
		break;
	case 'up':
		if ($user->data['is_member']) {
			redirect(s_link('my', 'profile'));
		} else if ($user->data['is_bot']) {
			redirect(s_link());
		}
		
		//
		// If the user submitted the form
		//
		$error = array();
		
		if (isset($_POST['submit'])) {
			require_once(ROOT . 'interfase/functions_validate.php');
			
			foreach ($fields as $k => $v) {
				$v_fields[$k] = request_var($k, $v);
			}
			
			//
			if (empty($v_fields['username'])) {
				$error['username'] = 'EMPTY_USERNAME';
			} else {
				$len_username = strlen($v_fields['username']);
				if (($len_username < 2) || ($len_username > 20) || !get_username_base($v_fields['username'], true)) {
					$error['username'] = 'USERNAME_INVALID';
				}
				
				if (!sizeof($error)) {
					$result = validate_username($v_fields['username']);
					if ($result['error']) {
						$error['username'] = $result['error_msg'];
					}
				}
				
				if (!sizeof($error)) {
					$v_fields['username_base'] = get_username_base($v_fields['username']);
					
					$sql = 'SELECT user_id
						FROM _members
						WHERE username_base = ?';
					if (sql_field(sql_filter($sql, $v_fields['username_base']), 'user_id', 0)) {
						$error['username'] = 'USERNAME_TAKEN';
					}
				}
				
				if (!sizeof($error)) {
					$sql = 'SELECT ub
						FROM _artists
						WHERE subdomain = ?';
					if (sql_field(sql_filter($sql, $v_fields['username_base']), 'ub', 0)) {
						$error['username'] = 'USERNAME_TAKEN';
					}
				}
			}
			
			if (!empty($v_fields['email'])) {
				$result = validate_email($v_fields['email']);
				if ($result['error']) {
					$error['email'] = $result['error_msg'];
				}
			} else {
				$error['email'] = 'EMPTY_EMAIL';
			}
			
			if (!empty($v_fields['key']) && !empty($v_fields['key_confirm'])) {
				if ($v_fields['key'] != $v_fields['key_confirm']) {
					$error['key'] = 'PASSWORD_MISMATCH';
				} else if (strlen($v_fields['key']) > 32) {
					$error['key'] = 'PASSWORD_LONG';
				}
			} else {
				if (empty($v_fields['key'])) {
					$error['key'] = 'EMPTY_PASSWORD';
				} elseif (empty($v_fields['key_confirm'])) {
					$error['key_confirm'] = 'EMPTY_PASSWORD_CONFIRM';
				}
			}
			
			if (!$v_fields['birthday_month'] || !$v_fields['birthday_day'] || !$v_fields['birthday_year']) {
				$error['birthday'] = 'EMPTY_BIRTH_MONTH';
			}
			
			if (!$v_fields['tos']) {
				$error['tos'] = 'AGREETOS_ERROR';
			}
			
			if (!sizeof($error)) {
				$v_fields['country'] = strtolower(geoip_country_code_by_name($user->ip));
				
				$v_fields['birthday'] = leading_zero($v_fields['birthday_year']) . leading_zero($v_fields['birthday_month']) . leading_zero($v_fields['birthday_day']);
				
				$member_data = array(
					'user_type' => USER_INACTIVE,
					'user_active' => 1,
					'username' => $v_fields['username'],
					'username_base' => $v_fields['username_base'],
					'user_password' => user_password($v_fields['key']),
					'user_regip' => $user->ip,
					'user_session_time' => 0,
					'user_lastpage' => '',
					'user_lastvisit' => time(),
					'user_regdate' => time(),
					'user_level' => 0,
					'user_posts' => 0,
					'userpage_posts' => 0,
					'user_points' => 0,
					'user_color' => '4D5358',
					'user_timezone' => $config['board_timezone'],
					'user_dst' => $config['board_dst'],
					'user_lang' => $config['default_lang'],
					'user_dateformat' => $config['default_dateformat'],
					'user_country' => (int) $v_fields['country'],
					'user_rank' => 0,
					'user_avatar' => '',
					'user_avatar_type' => 0,
					'user_email' => $v_fields['email'],
					'user_lastlogon' => 0,
					'user_totaltime' => 0,
					'user_totallogon' => 0,
					'user_totalpages' => 0,
					'user_gender' => $v_fields['gender'],
					'user_birthday' => (string) $v_fields['birthday'],
					'user_mark_items' => 0,
					'user_topic_order' => 0,
					'user_email_dc' => 1,
					'user_refop' => $v_fields['refop'],
					'user_refby' => $v_fields['refby']
				);
				$sql = 'INSERT INTO _members' . sql_build('INSERT', $member_data);
				$user_id = sql_query_nextid($sql);
				
				set_config('max_users', $config['max_users'] + 1);
				
				// Confirmation code
				$verification_code = md5(unique_id());
				
				$insert = array(
					'crypt_userid' => $user_id,
					'crypt_code' => $verification_code,
					'crypt_time' => $user->time
				);
				$sql = 'INSERT INTO _crypt_confirm' . sql_build('INSERT', $insert);
				sql_query($sql);
				
				// Emailer
				require_once(ROOT . 'interfase/emailer.php');
				$emailer = new emailer();
				
				// Pending points
				if ($v_fields['refop'] == 1 && !empty($v_fields['refby']) && !email_format($v_fields['refby'])) {
					$v_fields['refby'] = '';
				}
				
				if ($v_fields['refop'] == 1 && !empty($v_fields['refby'])) {
					$sql = 'SELECT user_id
						FROM _members
						WHERE user_email = ?';
					if ($ref_friend = sql_field(sql_filter($sql, $v_fields['refby']), 'user_id', 0)) {
						$sql_insert = array(
							'ref_uid' => $user_id,
							'ref_orig' => $ref_friend
						);
						$sql = 'INSERT INTO _members_ref_assoc' . sql_build('INSERT', $sql_insert);
						sql_query($sql);
					} else {
						$invite_user = explode('@', $v_fields['refby']);
						$invite_code = substr(md5(unique_id()), 0, 6);
						
						$sql_insert = array(
							'invite_code' => $invite_code,
							'invite_email' => $v_fields['refby'],
							'invite_uid' => $user_id
						);
						$sql = 'INSERT INTO _members_ref_invite' . sql_build('INSERT', $sql_insert);
						sql_query($sql);
						
						$emailer->from('Rock Republiik Networks <info@rockrepublik.net>');
						$emailer->use_template('user_invite');
						$emailer->email_address($v_fields['refby']);
						
						$emailer->assign_vars(array(
							'INVITED' => $invite_user[0],
							'USERNAME' => $v_fields['username'],
							'U_REGISTER' => s_link('my', array('register', 'a', $invite_code)))
						);
						$emailer->send();
						$emailer->reset();
					}
				}
				
				// Send confirm email
				$emailer->from('Rock Republik Networks <info@rockrepublik.net>');
				$emailer->use_template('user_welcome');
				$emailer->email_address($v_fields['email']);
				
				$emailer->assign_vars(array(
					'USERNAME' => $v_fields['username'],
					'U_ACTIVATE' => s_link('my', array('confirm', $verification_code)))
				);
				$emailer->send();
				$emailer->reset();
				
				$user->session_create($user_id);
				
				redirect(s_link('my', 'profile'));
			}
		}
		break;
	default:
		fatal_error();
		break;
}

//
// Signup data
//
if (sizeof($error))
{
	$template->assign_block_vars('error', array(
		'MESSAGE' => parse_error($error))
	);
}

foreach ($user->lang['MEMBERSHIP_BENEFITS2'] as $item)
{
	$template->assign_block_vars('list_benefits', array(
		'ITEM' => $item)
	);
}

$s_genres_select = '';
$genres = array(1 => 'MALE', 2 => 'FEMALE');
foreach ($genres as $id => $value)
{
	$s_genres_select .= '<option value="' . $id . '"' . (($v_fields['gender'] == $id) ? ' selected="true"' : '') . '>' . $user->lang[$value] . '</option>';
}

$s_bday_select = '<option value="">&nbsp;</option>';
for ($i = 1; $i < 32; $i++)
{
	$s_bday_select .= '<option value="' . $i . '"' . (($v_fields['birthday_day'] == $i) ? 'selected="true"' : '') . '>' . $i . '</option>';
}

$s_bmonth_select = '<option value="">&nbsp;</option>';
$months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
foreach ($months as $id => $value)
{
	$s_bmonth_select .= '<option value="' . $id . '"' . (($v_fields['birthday_month'] == $id) ? ' selected="true"' : '') . '>' . $user->lang['datetime'][$value] . '</option>';
}

$s_byear_select = '<option value="">&nbsp;</option>';
for ($i = 2005; $i > 1899; $i--)
{
	$s_byear_select .= '<option value="' . $i . '"' . (($v_fields['birthday_year'] == $i) ? ' selected="true"' : '') . '>' . $i . '</option>';
}

$tv = array(
	'U_ACTION' => s_link('my', 'register'),
	
	'V_USERNAME' => $v_fields['username'],
	'V_KEY' => $v_fields['key'],
	'V_KEY_CONFIRM' => $v_fields['key_confirm'],
	'V_EMAIL' => $v_fields['email'],
	'V_REFBY' => $v_fields['refby'],
	'V_GENDER' => $s_genres_select,
	'V_BIRTHDAY_DAY' => $s_bday_select,
	'V_BIRTHDAY_MONTH' => $s_bmonth_select,
	'V_BIRTHDAY_YEAR' => $s_byear_select,
	'V_TOS' => ($v_fields['tos']) ? ' checked="true"' : ''
);

if (isset($error['birthday']))
{
	$fields['birthday'] = true;
}

foreach ($fields as $k => $v)
{
	$tv['E_' . strtoupper($k)] = (isset($error[$k])) ? true : false;
}

//page_layout('NEW_ACCOUNT_SUBJECT', 'subscribe', $tv);

do_login();

?>