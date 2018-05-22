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
    if(global.JBWPageInit) {
        // call init
        global.JBWPageInit();
    }
})(typeof window !== 'undefined' ? window : this, function(window) {
    /**
     * Main construct.
     *
     * @param {Object} options
     * @return this
     */
    var JBWPage = function(options) {
        if(!(this instanceof JBWPage)) {
            return new JBWPage(options);
        }

        // call initialize xdm listener
        this.initializeXdmListener.call(this, options);

        // call initialize page
        this.initializePage.call(this, options);

        return this;
    }, widget = JBWPage.prototype;

    // page frame width
    widget.pageFrameWidth = '800';
    // page frame height
    widget.pageFrameHeight = '800';
    // page frame border
    widget.pageFrameBorder = 0;
    // page frame scrolling
    widget.pageScrolling = 'no';

    // page frame styles
    widget.pageFrameStyles = {
        'border' : 'none',
        'margin-top' : '20px',
        'width' : '100%'
    };

    // page styles
    widget.pageStyles = {
        'margin' : '0px auto',
        'width' : '1024px'
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
            // widget job title clicked?
            if(e.data.event == 'widget-job-modal-show'
            && e.data.from == 'career_page') {
                // create the modal
                return widget.createJobDetailModal(options, e.data);
            }
        }, false);

        return this;
    };

    /**
     * Initialize page.
     *
     * @param {Object} options
     * @return this
     */
    widget.initializePage = function(options) {
        // create the wrapper
        var wrapper = document.createElement('div');

        // set id
        wrapper.id = 'JBWWidgetPageContainer';

        // set wrapper style
        wrapper.style = widget.buildStyles(widget.pageStyles);

        // create the frame
        var frame = document.createElement('iframe');

        // set frame width
        frame.width         = widget.pageFrameWidth;
        // set frame height
        frame.height        = widget.pageFrameHeight;
        // set frame border
        frame.frameborder   = widget.pageFrameBorder;
        // set frame scrolling
        frame.scrolling     = widget.pageScrolling;
        // set frame style
        frame.style         = widget.buildStyles(widget.pageFrameStyles);

        // set unique id
        options.id = Math.random();

        // set frame src
        frame.src = widget.buildUrl(
            options.widget_root + '/plugins/widget/career-page', options);

        // get the body
        var body = document.getElementById(options.widget_selector);

        // append frame to wrapper
        wrapper.appendChild(frame);

        // append wrapper to body
        body.appendChild(wrapper);
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
        options.widget_type = 'career_page';
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

    window.JBWPage = JBWPage;
});