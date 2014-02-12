<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

require_once __DIR__ . '/../../../config.php';

global $CFG, $PAGE, $USER, $DB;

require_once $CFG->dirroot . '/blocks/video_converter/form/upload_form.php';
require_once $CFG->dirroot . '/blocks/video_converter/renderer.php';
require_once $CFG->dirroot . '/blocks/video_converter/entity/token.php';
require_once $CFG->dirroot . '/blocks/video_converter/entity/queue.php';

require_login();

$context = context_system::instance();

// Set up PAGE
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

// Obtain a token
$token = $token_manager->get_token_for_user($USER->id);

// Display the current queue
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
