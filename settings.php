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

$settings->add(new admin_setting_configtext(
        'block_video_converter/converterurl',
        new lang_string('converterurl', 'block_video_converter'),
        new lang_string('converterurldesc', 'block_video_converter'),
        '',
        PARAM_URL
));


$settings->add(new admin_setting_configtextarea(
        'block_video_converter/acceptedmimetypes',
        new lang_string('acceptedmimetypes', 'block_video_converter'),
        new lang_string('acceptedmimetypesdesc', 'block_video_converter'),
        'video/avi
         video/msvideo
         video/x-msvideo
         video/avs-video
         video/x-dv
         video/mpeg
         video/x-motion-jpeg
         video/quicktime
         video/x-sgi-movie
         video/x-mpeg
         video/x-mpeq2a
         video/x-qtc
         video/vnd.rn-realvideo
         video/x-scm
         application/x-shockwave-flash
        ',
        PARAM_RAW
));