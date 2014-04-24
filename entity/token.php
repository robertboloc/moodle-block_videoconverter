<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author    Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/entity.php');
require_once(__DIR__ . '/queue.php');

class token extends entity {

    /**
     * Makes sure a valid token exists for the user.
     */
    public function ensure_exists() {

        $this->clean_old_tokens();

        // Check that the user does not have a valid token already.
        if (!$this->has_valid_token()) {
            $this->create_token();
        }
    }

    /**
     * Checks if the provided token is valid.
     *
     * @param string $token
     * @return boolean
     */
    public function is_valid($token) {
        // Obtain users with tokens in use.
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

    /**
     * Obtains the token for the user. It will always return a valid token as it
     * ensures one exists.
     *
     * @param int $userid
     * @return string
     */
    public function get_token_for_user($userid) {
        $this->ensure_exists();

        $record = $this->db->get_record('block_vc_tokens', array(
            'userid' => $userid
        ));

        return $record->token;
    }

    /**
     * Creates a new token for the current logged in user.
     *
     * @global moxed $USER
     * @return int|boolean
     */
    public function create_token() {
        global $USER;

        $token = new stdClass();
        $token->userid = $USER->id;
        $token->token = sha1(time() + rand(0, 10000));
        $token->timecreated = time();
        $token->timeexpires = time() + 86400;

        return $this->db->insert_record('block_vc_tokens', $token);
    }

    /**
     * Checks if the current logged in user has a valid token.
     *
     * @global mixed $USER
     * @return boolean
     */
    public function has_valid_token() {
        global $USER;

        $token = $this->db->get_record('block_vc_tokens', array('userid' => $USER->id));

        $queued_items = $this->db->count_records('block_vc_queue', array(
            'userid' => $USER->id,
        ));

        if ($token && ($queued_items > 0 || (int) $token->timeexpires > time())) {
            return true;
        }

        return false;
    }

    /**
     * Obtains the user from the token.
     *
     * @param string $token
     * @return object
     */
    public function user_of_token($token) {
        return $this->db->get_record('block_vc_tokens', array(
            'token' => $token
        ), 'userid');
    }

    /**
     * Removes tokens not in use.
     */
    public function clean_old_tokens() {

        // Obtain users with tokens in use.
        $users = $this->db->get_records_sql(
            'SELECT
                 DISTINCT userid
             FROM
                 {block_vc_queue}
             WHERE status != ?
             ', array(queue::STATUS_HIDDEN)
        );

        if (!$users) {
            return true;
        }

        list($notInSql, $notInParams) = $this->db->get_in_or_equal(
            array_keys($users),
            SQL_PARAMS_QM,
            'param',
            false
        );

        $this->db->delete_records_select(
            'block_vc_tokens',
            "timeexpires < ? AND userid $notInSql",
            array_merge(array(time()), $notInParams)
        );
    }
}
