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

global $CFG, $PAGE, $USER, $DB;

require_once($CFG->dirroot . '/blocks/video_converter/form/upload_form.php');
require_once($CFG->dirroot . '/blocks/video_converter/renderer.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/token.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/queue.php');

require_login();

$context = context_system::instance();

// Set up $PAGE.
$PAGE->set_context($context);
$PAGE->set_url('/blocks/video_converter/lib/index.php', array());
$PAGE->set_pagetype('my-index');
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_title(get_string('convertnewvideo', 'block_video_converter'));
$PAGE->set_heading(get_string('convertnewvideo', 'block_video_converter'));
$PAGE->navbar->add(get_string('pluginname', 'block_video_converter'));
$PAGE->navbar->add(get_string('convertnewvideo', 'block_video_converter'));

echo $OUTPUT->header();

$queue_manager = new queue($DB);
$token_manager = new token($DB);

// Obtain a token.
$token = $token_manager->get_token_for_user($USER->id);

// Display the current queue.
$queue = $queue_manager->get_queue_for_user($USER->id);

$queue_renderer = $PAGE->get_renderer('block_video_converter');

echo $queue_renderer->user_queue($queue, $token);

echo $OUTPUT->box_start();

$form = new upload_form(null, array(
    'token' => $token
));

echo $form->display();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
