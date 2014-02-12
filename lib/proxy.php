<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

/**
 * Basic proxy that allows uploading video files to another domain.
 */

require_once __DIR__ . '/../../../config.php';

require_login();

global $CFG, $DB;

require_once $CFG->dirroot . '/blocks/video_converter/lib.php';
require_once $CFG->dirroot . '/blocks/video_converter/entity/token.php';

// First check that the token is valid
$token = required_param('token', PARAM_ALPHANUM);
$token_manager = new token($DB);

if(!$token_manager->is_valid($token)) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:accessdenied',
    ));
}

$method = $_SERVER['REQUEST_METHOD'];

// Only allow POST and video upload
if($method !== 'POST') {
    api_response(array(
        'status' => 'error',
        'message' => 'error:postonly',
    ));
}

// Only allow video file upload
if(!isset($_FILES['video'])) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:unknownfile',
    ));
}

// Check the size
if($_FILES['video']['size'] > $CFG->maxbytes) {
    api_response(array(
        'status' => 'error',
        'message' => 'error:filetoobig',
    ));
}

// Check the mime
$videoFileInfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $videoFileInfo->file($_FILES['video']['tmp_name']);

if(!mime_is_valid($mime)) {
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

// Configure cURL
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// Execute
curl_exec($ch);
curl_close($ch);
