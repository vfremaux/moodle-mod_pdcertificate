/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log'], function($, log) {

    var pdcertificate = {

        init: function() {
            $('.pdcertificate-select-all').bind('click', this.select_all);
            $('.pdcertificate-select-none').bind('click', this.unselect_all);

            log.debug("AMD PDCertificate initialized");
        },

        select_all: function() {
            $('.pdcertificate-sel').attr('checked', true);
        },

        unselect_all: function() {
            $('.pdcertificate-sel').attr('checked', null);
        },
    };

    return pdcertificate;
});
