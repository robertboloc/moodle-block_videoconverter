<?php

/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
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