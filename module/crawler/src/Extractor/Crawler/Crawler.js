/**
 * Object space
 */
module.exports = function() {
    this.construct.call(this);
};

/**
 * Static: Parent origin
 */
module.exports.origin = require('../Event/EventEmitter').prototype;

/**
 * Static: webpage
 */
module.exports.webpage = require('webpage');

/**
 * Static: Instantiator
 *
 * @return object
 */
module.exports.create = function() {
    return new module.exports();
};

/**
 * Inherit from parent origin
 */
module.exports.prototype = Object.create(module.exports.origin);

/**
 * Public: Constructor
 */
module.exports.prototype.construct = function() {
    module.exports.origin.construct.call(this);
    this.before = function() {};
    this.viewport = false;
    this.scripts = [];
};

/**
 * Public: Set browser view port
 *
 * @param *number width
 * @param *number height
 *
 * @return this
 */
module.exports.prototype.setViewPort = function(width, height) {
    this.viewport = {
        width: width,
        height: height
    };

    return this;
};

/**
 * Public: Adds a script to the DOM
 *
 * @param *string script
 *
 * @return this
 */
module.exports.prototype.addScript = function(script) {
    this.scripts.push(script);
    return this;
};

/**
 * Public: Init
 *
 * @param *function callback
 *
 * @return this
 */
module.exports.prototype.init = function(callback) {
    this.before = callback;
    return this;
};

/**
 * Public: Starts the crawl process
 *
 * @param *string   url
 * @param mixed     data
 * @param *function data
 *
 * @return this
 */
module.exports.prototype.crawl = function(url, data, callback) {
    if(typeof data === 'function') {
        callback = data;
        data = null;
    }

    var crawler = this;
    crawler.page = module.exports.webpage.create();

    if(crawler.viewport) {
        crawler.page.viewportSize = {
            width: this.viewport.width,
            height: this.viewport.height
        };
    }

    crawler.before(crawler);

    crawler.page.onError = function (message) {
        return crawler.emit('error', 'dom-js', message, crawler);
    };

    crawler.page.onAlert = function (message) {
        return crawler.emit('alert', message, crawler);
    };

    crawler.page.onConsoleMessage = function (message) {
        return crawler.emit('console', message, crawler);
    };

    crawler.page.onResourceTimeout = function(request) {
        return crawler.emit('timeout', request, crawler);
    };

    crawler.page.onResourceRequested = function (request, network) {
        return crawler.emit('request', request, network, crawler);
    };

    crawler.page.open(url, function (status) {
        if (status === 'fail') {
            crawler.emit('error', 'status', status, crawler);
        }

        if(crawler.scripts.length) {
            crawler.scripts.forEach(function(script, i) {
                //is this the last one?
                if(crawler.scripts.length == (i+1)) {
                    page.includeJs(script, function() {
                        crawler.emit('success', crawler.page.evaluate(callback, data));
                    });
                    return;
                }

                page.includeJs(script);
            });

            return;
        }

        crawler.emit('success', crawler.page.evaluate(callback, data));
    });

    return this;
};
