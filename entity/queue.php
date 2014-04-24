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

class queue extends entity {

    /**
     * Video is queued for conversion.
     */
    const STATUS_QUEUED     = 'queued';

    /**
     * Video is currently beeing converted.
     */
    const STATUS_CONVERTING = 'converting';

    /**
     * Video conversion failed.
     */
    const STATUS_FAILED     = 'failed';

    /**
     * Video conversion completed successfully
     */
    const STATUS_CONVERTED  = 'converted';

    /**
     * Video has been downloaded. The countdown to deletion begins.
     */
    const STATUS_DOWNLOADED = 'downloaded';

    /**
     * Video has been downloaded and it has expired. The record is kept for
     * logging.
     */
    const STATUS_HIDDEN     = 'hidden';

    /**
     * Returns the current queue for the provided user id.
     *
     * @param int $userid
     * @return mixed
     */
    public function get_queue_for_user($userid) {
        $sql = "
            SELECT
                bvq.id, name, hash, size, status, position, timeadded,
                timeupdated, timefinished, timedownloaded
            FROM
                {block_vc_queue} bvq JOIN
                {block_vc_files} bvf ON
                    bvq.fileid = bvf.id
            WHERE
                userid = ? AND
                status != ?
            ORDER BY timeadded ASC
         ";

        return $this->db->get_records_sql($sql, array(
            'userid' => $userid,
            'status' => self::STATUS_HIDDEN,
        ));
    }

    /**
     * Returns the last item in the generic (all users) queue. Used to determine
     * the global position in queue.
     *
     * @return object
     */
    public function get_last_in_queue() {
        $sql = "
            SELECT
                MAX(position) as last
            FROM
                {block_vc_queue}
            WHERE
                position != ?
        ";

        return $this->db->get_record_sql($sql, array(0));
    }

    /**
     * Adds an element to the queue.
     *
     * @param object $item
     * @return boolean
     */
    public function enqueue($item) {
        return $this->db->insert_record('block_vc_queue', $item);
    }

    /**
     * Updates the status of an item from the queue.
     *
     * @param int $queue_item_id
     * @param string $status
     * @param int $time
     * @return int
     */
    public function update_status($queue_item_id, $status, $time) {
        $params = array(
            'id' => $queue_item_id,
            'status' => $status,
        );

        switch ($status) {
            case self::STATUS_CONVERTED :
                $params['timefinished'] = $time;
                $params['position'] = 0;
                break;
            case self::STATUS_DOWNLOADED:
                $params['timedownloaded'] = $time;
                break;
            case self::STATUS_FAILED:
                $params['position'] = 0;
                break;
            default:
                $params['timeupdated'] = $time;
        }

        return $this->db->update_record('block_vc_queue', (object) $params);
    }

    /**
     * Returns the status of an item from the queue.
     *
     * @param int $queue_item_id
     * @return boolean
     */
    public function get_status($queue_item_id) {
        $item = $this->db->get_record('block_vc_queue', array(
            'id' => $queue_item_id,
        ));

        if ($item) {
            return $item;
        }

        return false;
    }
}
