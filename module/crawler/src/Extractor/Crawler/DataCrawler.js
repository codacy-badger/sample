/**
 * Object space
 */
module.exports = function() {
    this.construct.call(this);
};

/**
 * Static: Parent origin
 */
module.exports.origin = require('./Crawler').prototype;

/**
 * Static: Instantiator
 *
 * @return object
 */
module.exports.create = function() {
    return new module.exports();
};

/**
 * Static: Data extractor logic
 *
 * @return object
 */
module.exports.extractor = function(selectors) {
    var results = {},
        values = [],
        error = false,
        code,
        list;

    for (var key in selectors) {
        //this is the default value
        results[key] = selectors[key].auto || null;

        if (!selectors[key].selector) {
            continue;
        }

        //if selector eval, then evaluate it
        if (selectors[key].selector.indexOf('eval:') === 0) {
            code = selectors[key].selector.substr(5);
            try {
                eval(code);
            } catch (err) {
                //hard stop
                return {
                    error: true,
                    key: key,
                    message: 'eval error: ' + err,
                    code: code
                };
            }

            //and do nothing else
            continue;
        }

        values = [];
        list = document.querySelectorAll(selectors[key].selector);
        Array.prototype.slice.call(list).forEach(function(node, i) {
            //if there was an error
            if(error) {
                //we need to stop
                return;
            }

            //if selector eval, then evaluate it
            if (selectors[key].value.indexOf('eval:') === 0) {
                //we are doing it this way so evals can use this
                (function(i) {
                    code = selectors[key].value.substr(5);
                    try {
                        eval(code);
                    } catch (err) {
                        error = {
                            error: true,
                            key: key,
                            message: 'eval error: ' + err,
                            code: code
                        };

                        return;
                    }
                }).call(node, i);
                //and do nothing else
                return;
            }

            if (selectors[key].value === 'text') {
                values.push(node.innerText.trim());
                return;
            }

            if (selectors[key].value === 'html') {
                values.push(node.innerHTML.trim());
                return;
            }

            values.push(node[selectors[key].value]);
        });

        if(error) {
            return error;
        }

        //if no values
        if (!values.length) {
            //dont add to the result set
            continue;
        }

        //if we want to keep all the values
        if (selectors[key].multiple) {
            results[key] = values;
            continue;
        }

        //otherwise just use the first value
        results[key] = values[0];
    }

    return results;
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
    this.selectors = {};
};

/**
 * Public: imports selectors; defined for external datasets
 *
 * @param *object selectors
 *
 * @return this
 */
module.exports.prototype.import = function(selectors) {
    for(var name in selectors) {
        this.addExtractor(name, selectors[name])
    }
    return this;
};

/**
 * Public: Adds a data selector and extraction method
 *
 * @param *string name
 * @param *object options
 *
 * @return this
 */
module.exports.prototype.addExtractor = function(name, options) {
    if(typeof options.selector === 'function') {
        options.selector = options.selector.toString();

        var start = options.selector.indexOf('{') + 1;
        var length = options.selector.length - start - 1;
        options.selector = 'eval:' + options.selector.substr(start, length).trim();
    }

    if(typeof options.value === 'function') {
        options.value = options.value.toString();

        var start = options.value.indexOf('{') + 1;
        var length = options.value.length - start - 1;
        options.value = 'eval:' + options.value.substr(start, length).trim();
    }

    this.selectors[name] = {
        selector: options.selector,
        value: options.value,
        multiple: options.multiple || false,
        auto: options.auto || null
    };

    return this;
};

/**
 * Public: Starts the crawl process
 *
 * @param *string   url
 *
 * @return this
 */
module.exports.prototype.crawl = function(url) {
    module.exports.origin.crawl.call(this, url, this.selectors, module.exports.extractor);
    return this;
};
