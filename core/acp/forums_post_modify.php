<?php namespace App;

class __forums_post_modify extends mac {
    protected $object;

    public function __construct() {
        parent::__construct();

        $this->object = new \stdClass;

        $this->auth('mod');
    }

    public function home() {
        global $user, $cache, $comments;

        $this->id = request_var('msg_id', 0);

        $sql = 'SELECT *
            FROM _forum_posts
            WHERE post_id = ?';
        if (!$post = sql_fieldrow(sql_filter($sql, $this->id))) {
            fatal_error();
        }

        $this->object->post = (object) $post;

        $sql = 'SELECT *
            FROM _forum_topics
            WHERE topic_id = ?';
        if (!$topic = sql_fieldrow(sql_filter($sql, $this->object->post->topic_id))) {
            fatal_error();
        }

        $this->object->topic = (object) $topic;

        if (_button()) {
            $topic_title = request_var('topic_title', '');
            $post_message = $comments->prepare(request_var('message', '', true));

            if (!empty($topic_title) && $topic_title != $this->object->topic->topic_title) {
                $sql = 'UPDATE _forum_topics SET topic_title = ?
                    WHERE topic_id = ?';
                sql_query(sql_filter($sql, $topic_title, $this->object->topic->topic_id));

                $sql = 'SELECT id
                    FROM _events
                    WHERE event_topic = ?';
                if ($this->object->event_id = sql_field(sql_filter($sql, $this->object->topic->topic_id), 'id', 0)) {
                    $sql = 'UPDATE _events SET title = ?
                        WHERE id = ?';
                    sql_query(sql_filter($sql, $topic_title, $this->object->event_id));
                }
            }

            if ($post_message != $this->object->post->post_text) {
                $sql = 'UPDATE _forum_posts SET post_text = ?
                    WHERE post_id = ?';
                sql_query(sql_filter($sql, $post_message, $this->id));

                $rev = [
                    'rev_post' => $this->id,
                    'rev_uid'  => $user->d('user_id'),
                    'rev_time' => time(),
                    'rev_ip'   => $user->ip,
                    'rev_text' => $this->object->post->post_text
                ];
                sql_insert('forum_posts_rev', $rev);
            }

            redirect(s_link('post', $this->id));
        }

        v_style([
            'V_TOPIC'   => ($user->is('founder') && isset($this->object->topic->topic_title)) ? $this->object->topic->topic_title : '',
            'V_MESSAGE' => $this->object->post->post_text
        ]);

        //return page_layout('Editar', 'modcp.edit', $tv);
    }
}
