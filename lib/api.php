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

require_once(__DIR__ . '/../../../config.php');

global $CFG, $DB;

require_once($CFG->dirroot . '/blocks/video_converter/lib.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/token.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/file.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/queue.php');

$token = required_param('token', PARAM_ALPHANUM);
$request = required_param('request', PARAM_PATH);

// Managers.
$token_manager = new token($DB);
$file_manager = new file($DB);
$queue_manager = new queue($DB);

// First check that the token is valid.
if (!$token_manager->is_valid($token)) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:accessdenied',
    ));
}

switch($request) {
    /* TOKEN */
    case 'token.validate' :
        // We validated the token to get here...so just return true.
        api_response(array(
            'status' => 'success',
        ));
        break;
    case 'token.user' :

        $token_user = $token_manager->user_of_token($token);

        if ($token_user) {
            api_response(array(
                'status' => 'success',
                'data' => $token_user,
            ));
        } else {
            api_response(array(
                'status' => 'error',
                'message' => 'error:useroftokennotfound'
            ));
        }
        break;
    /* FILE */
    case 'file.create' :

        $file_name = filter_input(INPUT_POST, 'name');
        $file_hash = filter_input(INPUT_POST, 'hash');
        $file_size = filter_input(INPUT_POST, 'size');

        if ($file_name && $file_hash && $file_size) {

            $fileid = $file_manager->create_file_record((object) array(
                'name' => $file_name,
                'hash' => $file_hash,
                'size' => $file_size,
            ));

            api_response(array(
                'status' => 'success',
                'data' => array(
                    'fileid' => $fileid,
                ),
            ));
        }

        api_response(array(
            'status' => 'error',
            'message' => 'error:creatingfilerecord',
        ));
        break;
    /* QUEUE */
    case 'queue.new' :

        $userid = filter_input(INPUT_POST, 'userid');
        $fileid = filter_input(INPUT_POST, 'fileid');

        if ($userid && $fileid) {

            $queue_item = $queue_manager->get_last_in_queue();

            $position = ((int)$queue_item->last + 1);

            $queue_item_id = $queue_manager->enqueue((object)array(
                'userid' => $userid,
                'fileid' => $fileid,
                'position' => $position,
                'status' => queue::STATUS_QUEUED,
                'timeadded' => time(),
                'timeupdated' => time(),
                'timefinished' => 0,
                'timedownloaded' => 0,
            ));

            api_response(array(
                'status' => 'success',
                'data' => array(
                    'queue_item_id' => $queue_item_id,
                    'position' => $position,
                ),
            ));
        }

        api_response(array(
            'status' => 'error',
            'message' => 'error:enqueuefailed',
        ));
        break;
    case 'queue.remove' :
        $queue_item_id = filter_input(INPUT_POST, 'queue_item_id');

        if ($queue_item_id) {
            $queue_manager->update_status($queue_item_id, queue::STATUS_HIDDEN, time());

            api_response(array(
                'status' => 'success',
            ));
        }

        api_response(array(
            'status' => 'error',
            'message' => 'error:queueitemremove',
        ));
        break;
    case 'queue.status' :

        $queue_item_id = filter_input(INPUT_POST, 'queue_item_id');
        $queue_status = filter_input(INPUT_POST, 'queue_status');
        $time = filter_input(INPUT_POST, 'time');

        if ($queue_item_id && $queue_status && $time) {

            $queue_manager->update_status($queue_item_id, $queue_status, $time);

            api_response(array(
                'status' => 'success',
            ));
        }

        api_response(array(
            'status' => 'error',
            'message' => 'error:queueitemstatusupdatefailed',
        ));
        break;
    case 'queue.item' :

        $queue_item_id = filter_input(INPUT_GET, 'queue_item_id');

        if ($queue_item_id) {
            $item_status = $queue_manager->get_status($queue_item_id);

            if($item_status) {
                api_response(array(
                    'status' => 'success',
                    'data' => array(
                        'status' => $item_status->status,
                        'timeupdated' => $item->timeupdated,
                        'timedownloaded' => $item->timedownloaded,
                    ),
                ));
            }
        }

        api_response(array(
            'status' => 'error',
            'message' => 'error:queueitemidnotfound',
        ));
        break;
    default:
        api_response(array(
            'status' => 'error',
            'message' => 'error:unknownapirequest'
        ));
}
