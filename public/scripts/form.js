/**
 * Form to JSON - jQuery extension on converting form to json
 *
 * @version 0.0.1
 * @author Clark Galgo <cgalgo@openovate.com>
 */
jQuery.fn.extend({
    formToJson: function() {
        // get form values
        var data = this.serialize();
        // split it with &
        var splits = data.split('&');
        
        var json = {};
        
        /* recursive to json function
        -----------------------------------------------------*/
        var rToJson = function(key, value) {
            // split key with [
            var k = key.split('%5B');
            // loop keys
            for(var i = 0; i < k.length; i++) {
                // remove ] 
                k[i] = k[i].replace(/\%5D/g, '');
            }
            
            // reverse the keys
            k = k.reverse();
            for(var i = 0; i < k.length; i++) {
                // if key is empty, this must be an array
                if(k[i] == '') {
                    // initialize array
                    var obj = [];
                    // push value to array
                    obj.push(value);
                    // replace value
                    value = obj;
                    continue;
                }
                
                // else, this is an object
                // init object
                var obj = {}
                // store object value
                obj[k[i]] =  value;
                // replace value
                value = obj;
            }
            
            // return object
            return obj;
        };
        
        /* simple to json function
        ---------------------------------------------------------*/
        var toJson = function(str) {
            // split string with =
            var p = str.split('=');
            // if key string has [ char,
            // this should be recursive
            if(p[0].match(/\%5B/)) {
                // recursive json object
                return rToJson(p[0], decodeURIComponent(p[1]));
            }
            
            // init object
            var obj = {};
            // set object key and value
            obj[p[0]] = decodeURIComponent(p[1]);
            // return object
            return obj;
        };
        
        /* check if object is a simple array
        --------------------------------------------------------*/
        var isArray = function(obj){
            return !!obj && obj.constructor === Array;
        }
        
        /* recursive object merger 
        ---------------------------------------------------------*/
        var realMerge = function (to, from) {
            // loop from
            for(key in from) {
                // if value is an object
                if(typeof from[key] == 'object') {
                    // if to object with the same key is not object
                    if(typeof to[key] != 'object') {
                        // replace current value with from[key] value
                        to[key] = from[key];
                    // else if to object with the same key is a simple array
                    } else if(isArray(to[key])) {
                        // push the value to the array
                        to[key].push(from[key][0]);
                    } else {
                        // else, call recursive merge
                        to[key] = realMerge(to[key], from[key]);
                    }
                } else {
                    // set from to to
                    to[key] = from[key];
                }
            }
            
            // return merged object
            return to;
        };
        
        // loop splits
        for(var i = 0; i < splits.length; i++) {
            // call toJson function
            var tmp = toJson(splits[i]);
            // merge it
            json = realMerge(json, tmp);
        }
        
        return json;
    }
});