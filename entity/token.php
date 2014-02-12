<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

require_once __DIR__ . '/entity.php';
require_once __DIR__ . '/queue.php';

class token extends entity {

    public function ensure_exists() {

        $this->clean_old_tokens();

        // Check that the user does not have a valid token already
        if(!$this->has_valid_token()) {
            $this->create_token();
        }
    }

    public function is_valid($token) {
        // Obtain users with tokens in use
        $users = $this->db->get_records_sql(
            'SELECT
                 DISTINCT userid
             FROM
                 {block_vc_queue}
             WHERE status != ?
             ', array(queue::STATUS_HIDDEN)
        );

        $users_in = implode("','", array_keys($users));

        $select = "token = ? AND (timeexpires > ? OR userid IN ('$users_in'))";
        return $this->db->record_exists_select('block_vc_tokens', $select, array(
            $token,
            time(),
        ));
    }

    public function get_token_for_user($userid) {
        $this->ensure_exists();

        $record = $this->db->get_record('block_vc_tokens', array(
            'userid' => $userid
        ));

        return $record->token;
    }

    public function create_token() {
        global $USER;

        $token = new stdClass();
        $token->userid = $USER->id;
        $token->token = sha1(time() + rand(0, 10000));
        $token->timecreated = time();
        $token->timeexpires = time() + 86400;

        return $this->db->insert_record('block_vc_tokens', $token);
    }

    public function has_valid_token() {
        global $USER;

        $token = $this->db->get_record('block_vc_tokens', array('userid' => $USER->id));

        $queued_items = $this->db->count_records('block_vc_queue', array(
            'userid' => $USER->id,
        ));

        if($token && ($queued_items > 0 || (int)$token->timeexpires > time())) {
            return true;
        }

        return false;
    }

    public function user_of_token($token) {
        return $this->db->get_record('block_vc_tokens', array(
            'token' => $token
        ), 'userid');
    }

    /**
     * Only remove tokens not in use
     */
    public function clean_old_tokens() {

        // Obtain users with tokens in use
        $users = $this->db->get_records_sql(
            'SELECT
                 DISTINCT userid
             FROM
                 {block_vc_queue}
             WHERE status != ?
             ', array(queue::STATUS_HIDDEN)
        );

        $users_in = implode("','", array_keys($users));

        $this->db->delete_records_select(
            'block_vc_tokens',
            "timeexpires < ? AND userid NOT IN('$users_in')",
            array(time())
        );
    }
}