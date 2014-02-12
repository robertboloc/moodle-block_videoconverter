
/**
 * @author Robert Boloc <robert.boloc@urv.cat>
 * @copyright 2014 Servei de Recursos Educatius (http://www.sre.urv.cat)
 */

M.block_video_converter = {

    Y : null,
    transaction : [],
    maxbytes : null,

    init : function(Y, maxbytes) {
        this.Y = Y;
        this.maxbytes = maxbytes;
    },

    ajaxUploadVideo : function() {

        var notificationArea = Y.one('#block_video_converter-notification-area');

        // first clean up the notification area
        notificationArea.set('innerHTML', '');

        var video = document.getElementById('video');

        // Make sure the file is not empty
        if(video.value === '') {
            notificationArea.set(
                'innerHTML',
                '<span class="block_video_converter-color-red">' +
                    M.util.get_string('error:nofile', 'block_video_converter') +
                '<span>'
             );
            return false;
        }

        // Validate the size (if we can)
        if(video.files[0].size > this.maxbytes) {
            notificationArea.set(
                'innerHTML',
                '<span class="block_video_converter-color-red">' +
                    M.util.get_string('error:filetoobig', 'block_video_converter') +
                '<span>'
             );
            return false;
        }

        YUI().use("io-upload-iframe", "json-parse", function(Y) {

            var uploadButton = Y.one('#vc-up-btn');
            var uploadButtonContent = uploadButton.get('innerHTML');

            Y.io('proxy.php', {
                method : 'POST',
                form : {
                    id: 'block_video_converter_upload_form',
                    upload : true
                },
                data : '',
                on : {
                    // Replace the upload input with the ajax loader
                    start : function () {
                        uploadButton.set(
                            'innerHTML',
                            '<img class="icon" src="' +
                            M.cfg.wwwroot+'/blocks/video_converter/pix/ajax-loader.gif">' +
                            M.util.get_string('uploadingvideo', 'block_video_converter')
                        );
                        uploadButton.set('disabled', true); // no double clicks

                        // Notify user things might break if he/she leaves this page!
                        window.onbeforeunload = function(e) {
                            return M.util.get_string('confirmleave', 'block_video_converter');
                        };
                    },
                    complete : function (id, o) {

                        uploadButton.set('innerHTML', uploadButtonContent);
                        uploadButton.set('disabled', false); //enable clicking

                        var parsedResponse;

                        try {
                            var jsonResponse = Y.JSON.parse(o.responseText);

                            if(jsonResponse.status === 'error') {
                                parsedResponse = jsonResponse.message;
                            } else {
                                parsedResponse = 'success:fileuploaded';
                            }
                        }
                        catch (e) {
                            console.log(o.responseText);
                            parsedResponse = 'error:jsonparsing';
                        }

                        notificationArea.set(
                            'innerHTML',
                            M.util.get_string(parsedResponse, 'block_video_converter')
                        );

                        // Remove event and allow seamless navigation
                        window.onbeforeunload = null;

                        window.location.reload(true);
                    }
                }
            });
        });

        return false;
    }
};