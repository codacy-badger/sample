/**
 * Usage: phantomjs index.js [link] [base64]
 */
var system = require('system');
var link = system.args[1];
var code = require('./Extractor/Base64').decode(system.args[2]);

phantom.onError = function(message) {
    var data = { error: true, message: message };
    console.log('passing error...');
    console.log('--BOUNDARY');
    console.log(JSON.stringify(data));
    console.log('--BOUNDARY');
    phantom.exit(0);
};

require('./Extractor/Crawler/Crawler')
    .create()
    .on('console', function(message) {
        console.log(message);
    })
    .on('request', function(request, network, crawler) {
        //black list
        if (request.url.indexOf('facebook.com') !== -1
            || request.url.indexOf('twitter.com') !== -1
            || request.url.indexOf('facebook.net') !== -1
            || request.url.indexOf('google.com') !== -1
            || request.url.indexOf('youtube.com') !== -1
            || request.url.indexOf('google-analytics.com') !== -1
            || request.url.indexOf('googletagmanager.com') !== -1
            || request.url.indexOf('mixpanel.com') !== -1
            || request.url.indexOf('doubleclick.net') !== -1
            || request.url.indexOf('cdn.com') !== -1
            || request.url.indexOf('socialplus.com') !== -1
            || /\.js/ig.test(request.url)
            || /\.css/ig.test(request.url)
            || /(\.png)|(\.jpg)|(\.gif)|(\.svg)/ig.test(request.url)
        ) {
            network.cancel();
            return;
        }
    })
    .on('error', function(type, message) {
        if(type === 'dom-js') {
            console.log(message);
            return;
        }

        phantom.onError(message);
    })
    .on('success', function(data) {
        console.log('passing data...');
        console.log('--BOUNDARY');
        console.log(JSON.stringify(data));
        console.log('--BOUNDARY');
        phantom.exit();
    })
    .init(function() {
        this.page.settings.resourceTimeout = 300000;
        this.page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';
    })
    .crawl(link, code, function(code) {

        function extract(selector, value, multiple, auto) {
            var nodes = document.querySelectorAll(selector);
            var values = [].slice.call(nodes).map(function(node) {
                if(value === 'html') {
                    return node.innerHTML;
                }

                if(value === 'text') {
                    return node.innerText;
                }

                return node[value];
            });

            if(!values.length) {
                if(typeof auto !== 'undefined') {
                    return auto;
                }

                return null;
            }

            if(multiple) {
                return values;
            }

            if(!values[0].length) {
                if(typeof auto !== 'undefined') {
                    return auto;
                }

                return null;
            }

            return values[0];
        }

        eval('function extractor() { ' + code + '; }');

        return extractor();
    });
