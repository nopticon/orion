<?php namespace App;

class mac {
    public $_access;
    public $url;
    public $tv = [];

    protected $object;

    public function __construct() {
        return;
    }

    public function auth($name) {
        global $user;

        if (!$user->is($name)) {
            if (defined('_ACP')) {
                $this->_access = false;

                return false;
            }
            return fatal_error();
        }

        $this->_access = true;
        return true;
    }

    public function can() {
        return $this->_access;
    }

    public function isArtist() {
        global $user;

        if ($user->is('artist')) {
            $sql = 'SELECT a.ub
                FROM _artists_auth t
                INNER JOIN _artists a ON a.ub = t.ub
                WHERE t.user_id = ?';
            if ($artist_ary = sql_rowset(sql_filter($sql, $user->d('user_id')), false, 'ub')) {
                $sql_where = sql_filter('WHERE ub IN (??)', implode(',', $artist_ary));
            }
        }

        $artist = request_var('a', '');
        $module = request_var('module', '');
        $url    = s_link('acp', ['artist_select', 'r' => $module]);

        if (empty($artist)) {
            redirect($url);
        }

        if (!$this->object = get_artist($artist, true)) {
            fatal_error();
        }

        v_style([
            'ARTIST_SELECT' => $url,
            'ARTIST_NAME'   => $this->object['name']
        ]);

        return;
    }
}

class _acp {
    private $module;

    private $default_title = 'ACP';
    private $default_view = 'acp';

    public function __construct() {
        global $user;

        if (!$user->is('member')) {
            do_login();
        }

        if ($arg = request_var('args', '')) {
            foreach (explode('.', $arg) as $str_pair) {
                $pair = explode(':', $str_pair);

                if (isset($pair[0]) && isset($pair[1]) && !empty($pair[0])) {
                    $_REQUEST[$pair[0]] = $pair[1];
                }
            }
        }

        return;
    }

    public function getTitle() {
        return !empty($this->title) ? $this->title : $this->default_title;
    }

    public function getTemplate() {
        return !empty($this->template) ? $this->template : $this->default_view;
    }

    public function run() {
        $this->module = request_var('module', '');

        if (empty($this->module)) {
            return $this->rights();
        }

        if (!preg_match('#[a-z\_]+#i', $this->module)) {
            fatal_error();
        }

        $this->filepath = ROOT . 'acp/' . $this->module . '.php';

        if (!@file_exists($this->filepath)) {
            fatal_error();
        }

        require_once($this->filepath);

        $_object = __NAMESPACE__ . '\\__' . $this->module;
        if (!class_exists($_object)) {
            fatal_error();
        }

        $module = new $_object();

        $module->url = s_link() . substr(v_server('REQUEST_URI'), 1);
        $module->alias = $this->module;

        $module->home();

        if (!isset($module->template)) {
            $module->template = 'acp/' . $this->module;
        }

        $local_tv = [
            'MODULE_URL' => $module->url
        ];

        if (isset($module->tv)) {
            $local_tv = array_merge($local_tv, $module->tv);
        }

        $this->title = $this->module;
        $this->template = $module->template;

        // v_style($local_tv);
        //
        // _pre($this->getTemplate(), true);
        //
        // page_layout($this->getTitle(), $this->getTemplate());

        return v_style($local_tv);
    }

    private function rights() {
        $acp_dir = ROOT . 'acp/';

        $i = 0;

        $fp = @opendir($acp_dir);
        while ($row = @readdir($fp)) {
            if (!preg_match('#([a-z\_]+).php#i', $row, $part) || $row == '_template.php') {
                continue;
            }

            require_once($acp_dir . $row);

            $acp_alias = $part[1];
            $acp_upper = strtoupper($acp_alias);
            $object_name = __NAMESPACE__ . '\\__' . $acp_alias;

            if (!class_exists($object_name)) {
                continue;
            }

            if (!defined('_ACP')) {
                define('_ACP', true);
            }

            $object = new $object_name();

            if ($object->can()) {
                if (!$i) {
                    _style('acp_list');
                }

                switch ($acp_alias) {
                    case 'artist_select':
                        continue 2;
                }

                _style('acp_list.row', [
                    'URL'   => s_link('acp', $acp_alias),
                    'NAME'  => lang('ACP_' . $acp_alias, $acp_alias),
                    'IMAGE' => $acp_alias
                ]);

                $i++;
            }
        }
        @closedir($fp);

        return;
    }
}
