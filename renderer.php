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
require_once(__DIR__ . '/lib.php');

class block_video_converter_renderer extends plugin_renderer_base {

    public function user_queue(array $queue, $token) {

        if (empty($queue)) {
            global $OUTPUT;
            return $OUTPUT->box(get_string('queueempty', 'block_video_converter')) . '<br>';
        }

        $table = new html_table();

        $table->align = array(
            'center', 'center', 'center',
            'center', 'center', 'center',
            'center', 'center', 'center',
        );
        $table->width = "100%";

        $table->head = array(
            get_string('name', 'block_video_converter'),
            get_string('size', 'block_video_converter'),
            get_string('status', 'block_video_converter'),
            get_string('position', 'block_video_converter'),
            get_string('timeadded', 'block_video_converter'),
            get_string('timeupdated', 'block_video_converter'),
            get_string('timefinished', 'block_video_converter'),
            get_string('timedownloaded', 'block_video_converter'),
            get_string('action', 'block_video_converter'),
        );

        foreach ($queue as $row) {

            // Action.
            $action = '-';

            // Status.
            $status_class = '';
            switch($row->status) {
                case queue::STATUS_CONVERTED :
                case queue::STATUS_DOWNLOADED :
                    // Action.
                    $downloader = rtrim(get_config('block_video_converter', 'converterurl'), '/') . '/download.php';
                    $download_url = new moodle_url($downloader, array(
                        'token' => $token,
                        'file' => $row->hash,
                        'queue_item_id' => $row->id,
                    ));
                    $action = html_writer::link(
                        $download_url->out(false),
                        get_string('download', 'block_video_converter')
                    );

                    // Status.
                    $status_class = 'block_video_converter-badge-green';
                    break;
                case queue::STATUS_QUEUED :
                    $status_class = 'block_video_converter-badge-blue';
                    break;
                case queue::STATUS_CONVERTING :
                    $status_class = 'block_video_converter-badge-yellow';
                    break;
                case queue::STATUS_FAILED :
                    $status_class = 'block_video_converter-badge-red';
                    break;
            }

            $status = html_writer::tag(
                'span',
                get_string('status:' . $row->status, 'block_video_converter'),
                array('class' => $status_class)
            );

            $status_cell = new html_table_cell($status);
            $status_cell->attributes = array(
                'class' => 'block_video_converter-status-cell',
                'id' => 'block_video_converter-status-cell-' . $row->id,
                'data-id' => $row->id,
                'data-status' => $row->status,
            );

            $table->data[] = new html_table_row(array(
                $row->name,
                format_bytes($row->size),
                $status_cell,
                $row->status === queue::STATUS_CONVERTING ||
                $row->status === queue::STATUS_CONVERTED ||
                $row->status === queue::STATUS_DOWNLOADED ? '-' : $row->position,
                userdate($row->timeadded),
                !empty($row->timeupdated) ? userdate($row->timeupdated) : '-',
                $row->status === queue::STATUS_QUEUED ||
                $row->status === queue::STATUS_CONVERTING ||
                $row->status === queue::STATUS_FAILED ? '-' : userdate($row->timefinished),
                $row->status === queue::STATUS_DOWNLOADED ? userdate($row->timedownloaded) : '-',
                $action,
            ));
        }

        $refresh_script = "
            <script>
                window.onload = function(){
                    setInterval(function(){
                        M.block_video_converter.refreshStatuses('" . $token . "');
                    }, 5000);
                }
            </script>
        ";

        return html_writer::table($table) . $refresh_script;
    }
}