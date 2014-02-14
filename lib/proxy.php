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
 * Basic proxy that allows uploading video files to another domain.
 *
 * @author    Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();

global $CFG, $DB;

require_once($CFG->dirroot . '/blocks/video_converter/lib.php');
require_once($CFG->dirroot . '/blocks/video_converter/entity/token.php');

// First check that the token is valid.
$token = required_param('token', PARAM_ALPHANUM);
$token_manager = new token($DB);

if (!$token_manager->is_valid($token)) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:accessdenied',
    ));
}

$method = $_SERVER['REQUEST_METHOD'];

// Only allow POST and video upload.
if ($method !== 'POST') {
    api_response(array(
        'status' => 'error',
        'message' => 'error:postonly',
    ));
}

// Only allow video file upload.
if (!isset($_FILES['video'])) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:unknownfile',
    ));
}

// Check the size.
if ($_FILES['video']['size'] > $CFG->maxbytes) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:filetoobig',
    ));
}

// Check the mime.
if (!has_valid_mime($_FILES['video']['tmp_name'])) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:wrongmime',
    ));
}

$data = array(
    'video' => '@'. $_FILES['video']['tmp_name'] . ';filename='. $_FILES['video']['name'],
    'token' => $token,
);

// Only proxy to the converter.
$converter = rtrim(get_config('block_video_converter', 'converterurl'), '/') . '/upload.php';

$ch = curl_init($converter);

// Configure cURL.
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// Execute.
curl_exec($ch);
curl_close($ch);
