/*!
 * Jobayan Career Widget v1.0
 * https://jobayan.com
 *
 * Copyright Openovate Labs Inc.
 *
 * Date 2017-10-19T15:00Z
 */
(function(global, factory) {
    'use strict';

    if(!global.document) {
        throw new Error('Jobayan: Careers Widget requires a window with a document.');
    }

    // init factory
    factory(global);

    // if init is set
    if(global.JBWidgetInit) {
        // call init
        global.JBWidgetInit();
    }
})(typeof window !== 'undefined' ? window : this, function(window) {
    /**
     * Main construct.
     *
     * @param {Object} options
     * @return this
     */
    var JBWidget = function(options) {
        if(!(this instanceof JBWidget)) {
            return new JBWidget(options);
        }

        // call initialize xdm listener
        this.initializeXdmListener.call(this, options);

        // call initialize launcher
        this.initializeLauncher.call(this, options);

        return this;
    }, widget = JBWidget.prototype;

    // widget position
    widget.position = '{{widget_button_position}}';

    // launcher frame width
    widget.launcherFrameWidth = 200;
    // launcher frame height
    widget.launcherFrameHeight = 38;
    // launcher frame border
    widget.launcherFrameBorder = 0;
    // launcher frame scrolling
    widget.launcherScrolling = 'no';    

    // main frame width
    widget.mainFrameWidth = 400;
    // main frame height
    widget.mainFrameHeight = 350;
    // main frame border
    widget.mainFrameBorder = 0;
    // main frame scrolling
    widget.mainScrolling = 'no';

    // main launcher frame styles
    widget.launcherFrameStyles = {
        'border' : 'none'
    };

    // main frame styles
    widget.mainFrameStyles = {
        'border' : 'none'
    };

    // launcher styles
    widget.launcherStyles = {
        'bottom' : '0px',
        'position' : 'fixed',
        'transition' : 'all 1s ease 0s',
        'right' : '10px',
        'z-index' : '99999'
    };

    // main styles
    widget.mainStyles = {
        'bottom' : '-355px',
        'position' : 'fixed',
        'transition' : 'all 1s ease 0s',
        'right' : '10px',
        'z-index' : '99999'
    };

    /**
     * Initialize xdm iframe listener.
     *
     * @param {Object} options
     * @return this
     */
    widget.initializeXdmListener = function(options) {
        // create IE + others compatible event handler
        var method    = window.addEventListener ? 'addEventListener' : 'attachEvent';
        // look for the event
        var event     = window[method];
        // get the event name
        var eventName = method == 'attachEvent' ? 'onmessage' : 'message';
        
        // Listen to message from child window
        event(eventName, function(e) {
            // launcher click?
            if(e.data.event == 'launcher-toggle') {
                // toggle frames
                return widget.toggleFrames('launcher');
            }

            // widget close click?
            if(e.data.event == 'widget-close-toggle') {
                // toggle frames
                return widget.toggleFrames();
            }

            // widget job title clicked?
            if(e.data.event == 'widget-job-modal-show'
            && e.data.from == 'career_widget') {
                // create the modal
                return widget.createJobDetailModal(options, e.data);
            }
        }, false);

        return this;
    };

    /**
     * Initialize launcher.
     *
     * @param {Object} options
     * @return this
     */
    widget.initializeLauncher = function(options) {
        // create the wrapper
        var wrapper = document.createElement('div');

        // set id
        wrapper.id = 'JBWWidgetLauncherContainer';

        // check widget position
        if(widget.position == 'bottom-left') {
            // delete right position
            delete widget.launcherStyles['right'];

            // set left position
            widget.launcherStyles['left'] = '10px';
        }

        // set wrapper style
        wrapper.style = widget.buildStyles(widget.launcherStyles);

        // create the frame
        var frame = document.createElement('iframe');

        // set frame width
        frame.width         = widget.launcherFrameWidth;
        // set frame height
        frame.height        = widget.launcherFrameHeight;
        // set frame border
        frame.frameborder   = widget.launcherFrameBorder;
        // set frame scrolling
        frame.scrolling     = widget.launcherScrolling;
        // set frame style
        frame.style         = widget.buildStyles(widget.launcherFrameStyles);

        // set unique id
        options.id = Math.random();

        // set frame src
        frame.src = widget.buildUrl(
            options.widget_root + '/plugins/widget/career-launcher', options);

        // get the body
        var body = document.getElementsByTagName('body')[0];

        // append frame to wrapper
        wrapper.appendChild(frame);

        // append wrapper to body
        body.appendChild(wrapper);

        // initialize main widget
        widget.initializeMain(options);
    };

    /**
     * Initialize main widget.
     *
     * @param {Object} options
     * @return this
     */
    widget.initializeMain = function(options) {
        // create the wrapper
        var wrapper = document.createElement('div');

        // set id
        wrapper.id = 'JBWWidgetMainContainer';

        // check widget position
        if(widget.position == 'bottom-left') {
            // delete right position
            delete widget.mainStyles['right'];

            // set left position
            widget.mainStyles['left'] = '10px';
        }

        // set wrapper style
        wrapper.style = widget.buildStyles(widget.mainStyles);

        // create the frame
        var frame = document.createElement('iframe');

        // set frame id
        frame.id            = 'JBWWidgetMainFrame';
        // set frame width
        frame.width         = widget.mainFrameWidth;
        // set frame height
        frame.height        = widget.mainFrameHeight;
        // set frame border
        frame.frameborder   = widget.mainFrameBorder;
        // set frame scrolling
        frame.scrolling     = widget.mainScrolling;
        // set frame style
        frame.style         = widget.buildStyles(widget.mainFrameStyles);

        // set widget type
        options.widget_type = 'career_widget';

        // set frame src
        frame.src = widget.buildUrl(
            options.widget_root + '/plugins/widget/career-widget', options);

        // get the body
        var body = document.getElementsByTagName('body')[0];

        // append frame to wrapper
        wrapper.appendChild(frame);

        // append wrapper to body
        body.appendChild(wrapper);
    };

    /**
     * Toggle frames between the
     * launcher and the main widget.
     *
     * @param {String} target
     * @return this
     */
    widget.toggleFrames = function(target) {
        // get the launcher
        var launcher = document.getElementById('JBWWidgetLauncherContainer');
        // get the main frame
        var main = document.getElementById('JBWWidgetMainContainer');

        // if target toggle is from launcher
        if(target == 'launcher') {
            // update launcher style
            widget.launcherStyles.bottom = '-355px';
            // update main style
            widget.mainStyles.bottom = '0px';

            // set the launcher style
            launcher.style = widget.buildStyles(widget.launcherStyles);
            // set the main style
            main.style = widget.buildStyles(widget.mainStyles);

            return;
        }

        // update launcher style
        widget.launcherStyles.bottom = '0px';
        // update main style
        widget.mainStyles.bottom = '-355px';

        // set the launcher style
        launcher.style = widget.buildStyles(widget.launcherStyles);
        // set the main style
        main.style = widget.buildStyles(widget.mainStyles);
    };

    /**
     * Create the job detail modal.
     *
     * @param {Object} options
     * @param {Object} data
     * @return this
     */
    widget.createJobDetailModal = function(options, data) {
        // modal container styles
        var containerStyles = {
            'background' : 'rgba(0, 0, 0, 0.8)',
            'height' : '100%',
            'left' : '0px',
            'position' : 'fixed',
            'top' : '0px',
            'width' : '100%',
            'z-index' : '999999'
        };

        // modal container
        var container = document.createElement('div');

        // set id
        container.id    = 'JBWWidgetModalContainer';
        // set style
        container.style = widget.buildStyles(containerStyles); 

        // create the close button
        var closeButton = document.createElement('div');

        // set the id
        closeButton.id = 'JBWWidgetModalClose';
        // set the text
        closeButton.innerHTML = 'Close [x]';
        // set the style
        closeButton.style = widget.buildStyles({
            'cursor' : 'pointer',
            'color' : '#FFF',
            'font-size' : '13px',
            'font-weight' : '600',
            'position' : 'absolute',
            'right' : '1%',
            'top' : '1%',
            'text-decoration' : 'underline'
        });

        // on close button click
        closeButton.onclick = function() {
            // get the modal
            var modal = document.getElementById('JBWWidgetModalContainer');

            // remove the modal
            modal.parentNode.removeChild(modal);
        };

        // append the close button
        container.appendChild(closeButton);

        // create the iframe
        var frame = document.createElement('iframe');

        // set the frame id
        frame.id = 'JBWWidgetModalFrame';

        // set widget type
        options.widget_type = 'career_widget';
        // set the post id
        options.post_id = data.post;

        // set frame src
        frame.src = widget.buildUrl(
            options.widget_root + '/plugins/widget/post/detail/' + data.post, options);

        // set the style
        frame.style = widget.buildStyles({
            'background' : '#FFF',
            'border' : 'none',
            'left' : '15%',
            'position' : 'absolute',
            'height' : '100%',
            'width' : '70%'
        });

        // append the frame
        container.appendChild(frame);

        // get the body
        var body = document.getElementsByTagName('body')[0];

        // append the container
        body.appendChild(container);
    };

    /**
     * Build the object styles as stirng.
     *
     * @param {Object} styles
     * @return this
     */
    widget.buildStyles = function(styles) {
        // style string
        var style = '';

        // iterate on each styles
        for(var i in styles) {
            style += i + ':' + styles[i] + ';';
        }

        return style;
    };

    /**
     * Build url and parameters
     *
     * @param {String} url
     * @param {Object} params
     * @return this
     */
    widget.buildUrl = function(url, params) {
        // parameter string
        var paramString = [];

        // iterate on each parameters
        for(var i in params) {
            paramString.push(i + '=' + params[i]);
        }

        return '//' + url + '?' + paramString.join('&');
    };

    window.JBWidget = JBWidget;
});