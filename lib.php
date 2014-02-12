<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

function api_response(array $response) {
    echo json_encode($response);
    exit;
}

function format_bytes($size, $precision = 2) {
    $base = log($size) / log(1024);
    $suffixes = array('b', 'kb', 'mb', 'gb', 'tb');

    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

function mime_is_valid($mime) {
    $accepted_mimes_string = get_config('block_video_converter', 'acceptedmimetypes');

    $accepted_mimes = explode("\n", str_replace(" ", '', $accepted_mimes_string));

    $clean_accepted_mimes = array();
    foreach($accepted_mimes as $accepted_mime) {
        $clean_accepted_mimes[trim($accepted_mime)] = true;
    }

    return isset($clean_accepted_mimes[$mime]);
}
