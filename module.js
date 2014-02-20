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
    },

    refreshStatuses : function (token) {

        // Obtain all the refreshable ids
        var ids = Y.all('.block_video_converter-row');

        ids.each(function(node) {
            // Obtain the status
            var statusCell = node.one('.block_video_converter-cell-status');
            var actionCell = node.one('.block_video_converter-cell-action');
            var positionCell = node.one('.block_video_converter-cell-position');
            var timeupdatedCell = node.one('.block_video_converter-cell-timeupdated');
            var timefinishedCell = node.one('.block_video_converter-cell-timefinished');
            var status = statusCell.getAttribute('data-status');

            // If in a final state do nothing
            if(status !== 'downloaded' && status !== 'converted' && status !== 'failed') {
                /**
                 * Obtain the queue item id. Because of a bug in the moodle
                 * table renderer (can not set custom attributes on a table row)
                 * we use the status cell to store the queue item id.
                 * When the bug is fixed we should move this data item to the
                 * table row.
                 * https://tracker.moodle.org/browse/MDL-39030
                 **/
                var id = statusCell.getAttribute('data-id');

                // Obtain the updated status
                Y.io('api.php', {
                    method : 'GET',
                    data : 'token=' + token + '&request=queue.item&queue_item_id=' + id,
                    on : {
                        complete : function(retid, o) {

                            var updatedStatus;

                            try {
                                var jsonResponse = Y.JSON.parse(o.response);

                                if (jsonResponse.status === 'success') {

                                    updatedStatus = jsonResponse.data.status;

                                    // Check if status changed
                                    if (status !== updatedStatus) {

                                        var statusNode = statusCell.one('.block_video_converter-cell-status-content');
                                        var actionNode = actionCell.one('.block_video_converter-cell-action-content');

                                        statusNode.set('innerHTML', M.util.get_string('status:' + updatedStatus, 'block_video_converter'));

                                        // Update timeupdated always
                                        timeupdatedCell.setHTML(jsonResponse.data.timeupdated);

                                        switch(updatedStatus) {
                                            case 'converting' :
                                                statusNode.setAttribute('class', 'block_video_converter-cell-status-content block_video_converter-badge-yellow');
                                                positionCell.setHTML(jsonResponse.data.position);
                                                break;
                                            case 'failed' :
                                                statusNode.setAttribute('class', 'block_video_converter-cell-status-content block_video_converter-badge-red');
                                                positionCell.setHTML('-');
                                                break;
                                            case 'converted' :
                                                statusNode.setAttribute('class', 'block_video_converter-cell-status-content block_video_converter-badge-green');
                                                timefinishedCell.setHTML(jsonResponse.data.timefinished);
                                                actionNode.removeClass('block_video_converter-hidden');
                                                positionCell.setHTML('-');
                                                break;
                                            default:
                                        }
                                    }
                                }
                            } catch (e) {}
                        }
                    }
                });
            }
        });
    }
};