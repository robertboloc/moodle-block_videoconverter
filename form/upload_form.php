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

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class upload_form extends moodleform {

    protected function definition() {
        global $PAGE, $CFG;

        $params = array($CFG->maxbytes);

        // Load the JS module.
        $PAGE->requires->js_init_call('M.block_video_converter.init', $params);

        // Load the translations.
        $PAGE->requires->string_for_js('confirmleave', 'block_video_converter');
        $PAGE->requires->string_for_js('uploadingvideo', 'block_video_converter');
        $PAGE->requires->string_for_js('error:filetoobig', 'block_video_converter');
        $PAGE->requires->string_for_js('error:nofile', 'block_video_converter');
        $PAGE->requires->string_for_js('error:accessdenied', 'block_video_converter');
        $PAGE->requires->string_for_js('error:postonly', 'block_video_converter');
        $PAGE->requires->string_for_js('error:jsonparsing', 'block_video_converter');
        $PAGE->requires->string_for_js('error:unknownfile', 'block_video_converter');
        $PAGE->requires->string_for_js('error:invalidparams', 'block_video_converter');
        $PAGE->requires->string_for_js('error:nofilesent', 'block_video_converter');
        $PAGE->requires->string_for_js('error:unknownerror', 'block_video_converter');
        $PAGE->requires->string_for_js('error:unknownapirequest', 'block_video_converter');
        $PAGE->requires->string_for_js('error:useroftokennotfound', 'block_video_converter');
        $PAGE->requires->string_for_js('error:movingfile', 'block_video_converter');
        $PAGE->requires->string_for_js('error:enqueuefailed', 'block_video_converter');
        $PAGE->requires->string_for_js('error:wrongmime', 'block_video_converter');
        $PAGE->requires->string_for_js('error:queueitemstatusupdatefailed', 'block_video_converter');
        $PAGE->requires->string_for_js('error:queueitemremove', 'block_video_converter');
        $PAGE->requires->string_for_js('success:fileuploaded', 'block_video_converter');

        $form = $this->_form;

        $attributes = $form->getAttributes();

        $form->setAttributes(array_replace($attributes, array(
            'enctype' =>"multipart/form-data",
            'id' => 'block_video_converter_upload_form',
        )));

        $form->addElement(
            'html',
            '<div id="block_video_converter-upload-form">
                 <h4>' . get_string('uploadnewvideo', 'block_video_converter') . '</h4>
                 <input name="video" id="video" type="file">
                 <button id="vc-up-btn"
                         onclick="return M.block_video_converter.ajaxUploadVideo()">'.
                     get_string('uploadvideo', 'block_video_converter').'
                 </button>
                 <div id="block_video_converter-notification-area"></div>
            </div>'
        );

        $form->addElement('hidden', 'token', $this->_customdata['token']);
    }
}