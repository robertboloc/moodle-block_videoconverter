<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

require_once __DIR__ . '/entity/token.php';

class block_video_converter extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_video_converter');
    }

    function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->items  =  array();
        $this->content->icons  =  array();
        $this->content->footer = '';

        $context = context_system::instance();

        if(!has_capability('block/video_converter:convertvideo', $context)) {
            $this->content = '';
            return $this->content;
        }

        $this->content->items[] = html_writer::link(
            new moodle_url('/blocks/video_converter/lib/index.php'),
            get_string('convertnewvideo', 'block_video_converter')
        );
        $this->content->icons[] = html_writer::tag('img', '', array(
            'src'    => $CFG->wwwroot . '/blocks/video_converter/pix/icon.png',
            'alt'    => get_string('convertnewvideo', 'block_video_converter'),
            'class'  => 'icon',
            'width'  => '16',
            'height' => '16',
        ));

        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'my' => true,
        );
    }

    public function has_config() {
        return true;
    }
}