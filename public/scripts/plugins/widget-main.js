jQuery(function($) {
    //
    // General Configuration
    //
    var config = {};

    // 
    // General Endpoints
    //
    var endpoints = {};

    // 
    // Maximum posting per page
    //
    var range = 5;

    //
    // Widget Loaded flag
    var loaded = false;

    //
    // Main initializer
    //
    window.WidgetMain = function() {
        // if widget already loaded
        if(loaded) {
            return;
        }

        // get the key
        var key  = $('#JBWidgetFrame').attr('data-key');
        // get the widget type
        var type = $('#JBWidgetFrame').attr('data-type');
        // get the widget root
        var root = $('#JBWidgetFrame').attr('data-root');

        // set the config
        config = {
            widget_key  : key,
            widget_type : type,
            widget_root : root,
            start       : 0
        };

        // set the job listing endpoint
        endpoints.profile_posts = '//' + root + '/plugins/widget/post/search';
    }

    //
    // On widget flash init
    //
    $(window).on('widget-flash-init', function(e) {
        // remove flash
        setTimeout(function() {
            // fade out
            $('#JBFlash').fadeOut();
        }, 3000);
    });

    //
    // On widget flash close
    //
    $(window).on('widget-flash-close-click', function(e) {
        // fade out
        $('#JBFlash').fadeOut();
    });

    //
    // On window message.
    //
    $(window).on('message', function(e) {
        // get the event
        e = e.originalEvent;
    });

    //
    // On widget button launcher click
    //
    $(window).on('widget-button-launcher-click', function(e) {
        // let the parent know
        window.parent.postMessage({
            event : 'launcher-toggle'
        }, '*');
    });

    //
    // On widget close click
    //
    $(window).on('widget-close-click', function(e) {
        // let the parent know
        window.parent.postMessage({
            event : 'widget-close-toggle'
        }, '*');
    });

    //
    // On widget job title click
    //
    $(window).on('widget-job-title-click', function(e) {
        // prevent default
        e.preventDefault();

        // get the post id
        var post = $(e.target).attr('data-post');

        // let the parent know
        window.parent.postMessage({
            event : 'widget-job-modal-show',
            post  : post,
            from  : $('html').attr('data-type')
        }, '*');
    });

    //
    // On load more click
    //
    $(window).on('widget-load-more-click', function(e) {
        // increment start
        config.start += 1;

        // call load job posts
        loadJobPosts();
    });

    //
    // On apply form submit
    //
    $(window).on('widget-apply-form-submit', function(e) {
        // show loader
        $('#JBJobDetailModal .widget-form-loader').removeClass('hide');
    });

    //
    // Load the job posting
    //
    var loadJobPosts = function(more) {
        // get the selector
        var selector = null;

        // if type is widget
        if(config.widget_type == 'career_widget') {
            selector = '#JBCareerWidget';
        } else {
            selector = '#JBCareerPage';
        }

        // build the request url
        var url = buildUrl(endpoints.profile_posts, config);

        // hide loader
        $(selector).find('.widget-loader').removeClass('hide');

        // send the request
        $.get(url, function(response) {
            // is there an error?
            if(response.error || !response.results.template) {
                // show load more
                $(selector).find('.widget-load-more').addClass('hide');

                // hide loader
                $(selector).find('.widget-loader').addClass('hide');

                return;
            }

            setTimeout(function() {
                // append template
                $(selector).find('.widget-items').append(response.results.template);

                // show load more
                $(selector).find('.widget-load-more').removeClass('hide');

                // hide loader
                $(selector).find('.widget-loader').addClass('hide');

                // re-initialize doon
                $(selector).doon();
            }, 1000);
        });
    };

    //
    // Builds the given url with
    // the given parameters.
    //
    var buildUrl = function(url, params) {
        // parameter string
        var paramString = [];

        // iterate on each parameters
        for(var i in params) {
            paramString.push(i + '=' + params[i]);
        }

        return url + '?' + paramString.join('&');
    };

    // initialize widget
    window.WidgetMain();

    //
    // Hide load more
    //
    $(window).on('hide-load-more-init', function(e) {
        $('.widget-load-more').hide();
    });
    
    // activate all scripts
    $(document.body).doon();
});