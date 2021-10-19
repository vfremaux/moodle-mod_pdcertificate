/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log', 'core/config'], function($, log, cfg) {

    var pdcertificate = {

        init: function() {
            $('.pdcertificate-select-all').bind('click', this.select_all);
            $('.pdcertificate-select-none').bind('click', this.unselect_all);
            $('.pdcertificate-time-override').bind('change', this.send_time_override);

            log.debug("AMD PDCertificate initialized 2021100800.001");
        },

        select_all: function(e) {
            e.stopPropagation();
            $('.pdcertificate-sel').attr('checked', true);
            return false;
        },

        unselect_all: function(e) {
            e.stopPropagation();
            $('.pdcertificate-sel').attr('checked', null);
            return false;
        },

        send_time_override: function(e) {
            e.stopPropagation();

            var that = $(this);

            // Todo : send Ajax service to regisqter the time override value.
            var url = cfg.wwwroot + '/mod/pdcertificate/ajax/service.php';
            url += '?iid=' + that.attr('data-iid');
            url += '&to=' + that.val();
            url += '&sesskey=' + cfg.sesskey;

            $.get(url, function(data) {
                // just toggle quickly color to tell we are done.
                $('#id-timeoverride-' + data).css('background-color: #008000; color: white');
                $.sleep(500);
                $('#id-timeoverride-' + data).css('background-color: initial; color: initial');
            }, 'html');

            return false;
        }
    };

    return pdcertificate;
});
