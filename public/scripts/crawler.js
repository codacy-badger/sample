jQuery(function($) {
    /**
     * Notifier
     */
    (function() {
        $(window).on('notify-init', function(e, trigger) {
            setTimeout(function() {
                $(trigger).fadeOut('fast', function() {
                    $(trigger).remove();
                });
            }, 3000);
        });

        var template = '<div data-do="notify" class="notify notify-{TYPE}"><span class="message">{MESSAGE}</span></div>';

        $.extend({
            notify: function(message, type) {
                type = type || 'info';

                var notification = $(template.replace('{TYPE}', type).replace('{MESSAGE}', message));
                $(document.body).append(notification);
                notification.doon()
            }
        })

    })();

    //activate all scripts
    $(document.body).doon();
});
