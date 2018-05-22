/**
 * Object space
 */
module.exports = function() {
    this.construct.call(this);
};

/**
 * Static: Instantiator
 *
 * @return object
 */
module.exports.create = function() {
    return new module.exports();
};

/**
 * Public: Constructor
 */
module.exports.prototype.construct = function() {
    this.listeners = {};
};

/**
 * Public: Listens to an event
 *
 * @param *string   event
 * @param *function callback
 *
 * @return this
 */
module.exports.prototype.on = function(event, callback) {
    if(!(this.listeners[event] instanceof Array)) {
        this.listeners[event] = [];
    }

    this.listeners[event].push({
        callback: callback,
        once: false
    });

    return this;
};

/**
 * Public: Listens to an event once
 *
 * @param *string   event
 * @param *function callback
 *
 * @return this
 */
module.exports.prototype.once = function(event, callback) {
    if(!(this.listeners[event] instanceof Array)) {
        this.listeners[event] = [];
    }

    this.listeners[event].push({
        callback: callback,
        once: true
    });

    return this;
};

/**
 * Public: Triggers to an event
 *
 * @param *string event
 * @param mixed
 *
 * @return this
 */
module.exports.prototype.emit = function() {
    var args = Array.prototype.slice.call(arguments);
    var event = args.shift();

    //if it doesn't exist
    if(!(this.listeners[event] instanceof Array)) {
        return this;
    }

    var success = true;
    this.listeners[event].forEach(function(listener, i) {
        if(!success) {
            return;
        }

        success = listener.callback.apply(this, args) !== false;

        if(listener.once) {
            delete this.listeners[event][i];
        }
    });

    return success;
};

/**
 * Public: Triggers to an event, syncronously
 *
 * @param *string event
 * @param mixed
 *
 * @return this
 */
module.exports.prototype.sync = function() {
    var args = Array.prototype.slice.call(arguments);
    var event = args.shift();

    //if it doesn't exist
    if(!(this.listeners[event] instanceof Array)) {
        return this;
    }

    var sync = require('./Sync')().scope(this);

    this.listeners[event].forEach(function(listener, i) {
        sync.then(function(next) {
            var apply = Array.prototype.slice.call(args);
            apply.push(next);
            listener.callback.apply(this, apply);
        });

        if(listener.once) {
            delete this.listeners[event][i];
        }
    }.bind(this));

    return sync;
};
