/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var pdcertificate = {

        init: function() {
            $('.pdcertificate-select-all').bind('click', this.select_all);
            $('.pdcertificate-select-none').bind('click', this.unselect_all);
            $('.pdcertificate-sel').bind('click', this.toggle_one);
            $('.pdcertificate-time-override').bind('change', this.send_time_override);

            log.debug("AMD PDCertificate initialized 2021100800.001");
        },

        select_all: function(e) {
            e.stopPropagation();
            $('.pdcertificate-sel').attr('checked', true);
            $('#id-pdcertificate-select-action').prop('disabled', null);
            return false;
        },

        unselect_all: function(e) {
            e.stopPropagation();
            $('.pdcertificate-sel').attr('checked', null);
            $('#id-pdcertificate-select-action').prop('disabled', 'disabled');
            return false;
        },

        // enable the action list only if at least one is selected.
        toggle_one: function(e) {
            e.stopPropagation();
            var that = $(this);
            if (that.prop('checked')) {
                $('#id-pdcertificate-select-action').prop('disabled', null);
            } else {
                $('#id-pdcertificate-select-action').prop('disabled', 'disabled');
            }
        },

        send_time_override: function(e) {
            e.stopPropagation();

            var that = $(this);

            // Todo : send Ajax service to regisqter the time override value.
            var url = cfg.wwwroot + '/mod/pdcertificate/ajax/service.php';
            url += '?iid=' + that.attr('data-iid');
            url += '&what=overridetime';
            url += '&to=' + that.val();
            url += '&sesskey=' + cfg.sesskey;

            $.get(url, function(data) {
            	log.debug("Returned " + data);
                // just toggle quickly color to tell we are done.
                $('#id-timeoverride-' + data).css('background-color: #008000; color: white');
                $.delay(500);
                $('#id-timeoverride-' + data).css('background-color: initial; color: initial');
            }, 'html');

            return false;
        }
    };

    return pdcertificate;
});
