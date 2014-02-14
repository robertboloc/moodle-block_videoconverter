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

function api_response(array $response) {
    echo json_encode($response);
    exit;
}

function format_bytes($size, $precision = 2) {
    $base = log($size) / log(1024);
    $suffixes = array('b', 'kb', 'mb', 'gb', 'tb');

    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

function has_valid_mime($file) {

    $raw_mime = exec('file --mime-type '. $file);

    $mime = str_replace(array($file . ':', ' '), '', $raw_mime);

    $accepted_mimes_string = get_config('block_video_converter', 'acceptedmimetypes');

    $accepted_mimes = explode("\n", str_replace(" ", '', $accepted_mimes_string));

    $clean_accepted_mimes = array();
    foreach ($accepted_mimes as $accepted_mime) {
        $clean_accepted_mimes[trim($accepted_mime)] = true;
    }

    return isset($clean_accepted_mimes[$mime]);
}
