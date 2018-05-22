jQuery(function($) {
    /**
     * General Forms
     */
    (function() {
        /**
         * Tag Field
         */
        $(window).on('tag-field-init', function(e, target) {
            target = $(target);

            var name = target.attr('data-name') || 'tags';
            var placeholder = target.attr('data-placeholder') || 'Tag';

            //TEMPLATES
            var tagTemplate = '<div class="tag"><input type="text" class="tag-input'
            + ' text-field" name="' + name + '[]" placeholder="'
            + placeholder + '" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';

            var addResize = function(filter) {
                var input = $('input[type=text]', filter);

                input.keyup(function() {
                    var value = input.val() || input.attr('placeholder');

                    var test = $('<span>').append(value).css({
                        visibility: 'hidden',
                        position: 'absolute',
                        top: 0, left: 0
                    }).appendTo('header:first');

                    var width = test.width() + 10;

                    if((width + 40) > target.width()) {
                        width = target.width() - 40;
                    }

                    $(this).width(width);
                    test.remove();
                }).trigger('keyup');
            };

            var addRemove = function(filter) {
                $('a.remove', filter).click(function() {
                    var val = $('input', filter).val();

                    $(this).parent().remove();
                });
            };

            //INITITALIZERS
            var initTag = function(filter) {
                addRemove(filter);
                addResize(filter);

                $('input', filter).blur(function() {
                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        //remove it
                        $(this).next().click();
                    }

                    var count = 0;
                    var currentTagValue = $(this).val();
                    $('div.tag input', target).each(function() {
                        if(currentTagValue === $(this).val()) {
                            count++;
                        }
                    });

                    if(count > 1) {
                        $(this).parent().remove();
                    }
                });
            };

            //EVENTS
            target.click(function(e) {
                if($(e.target).hasClass('tag-field')) {
                    var last = $('div.tag:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(tagTemplate);
                        target.append(last);

                        initTag(last);
                    }

                    $('input', last).focus();
                }
            });

            //INITIALIZE
            $('div.tag', target).each(function() {
                initTag($(this));
            });
        });

        /**
         * Keyword Field
         */
        $(window).on('keyword-field-init', function(e, target) {
            //translations
            try {
                var translations = JSON.parse($('#keyword-translations').html());
            } catch(e) {
                var translations = {};
            }

            [
                'Keyword'
            ].forEach(function(translation) {
                translations[translation] = translations[translation] || translation;
            });

            target = $(target);

            //TEMPLATES
            var keywordTemplate = '<div class="keyword"><input type="text" class="keyword-input'
            + ' text-field" name="blog_keywords[]" placeholder="'
            + translations['Keyword']+'" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';


            var addResize = function(filter) {
                var input = $('input[type=text]', filter);

                input.keyup(function() {
                    var value = input.val() || input.attr('placeholder');

                    var test = $('<span>').append(value).css({
                        visibility: 'hidden',
                        position: 'absolute',
                        top: 0, left: 0
                    }).appendTo('header:first');

                    var width = test.width() + 10;

                    if((width + 40) > target.width()) {
                        width = target.width() - 40;
                    }

                    $(this).width(width);
                    test.remove();
                }).trigger('keyup');
            };

            var addRemove = function(filter) {
                $('a.remove', filter).click(function() {
                    var val = $('input', filter).val();

                    $(this).parent().remove();
                });
            };

            //INITITALIZERS
            var initKeyword = function(filter) {
                addRemove(filter);
                addResize(filter);

                $('input', filter).blur(function() {
                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        //remove it
                        $(this).next().click();
                    }

                    var count = 0;
                    var currentKeywordValue = $(this).val();
                    $('div.keyword input', target).each(function() {
                        if(currentKeywordValue === $(this).val()) {
                            count++;
                        }
                    });

                    if(count > 1) {
                        $(this).parent().remove();
                    }
                });
            };

            //EVENTS
            target.click(function(e) {
                if($(e.target).hasClass('keyword-field')) {
                    var last = $('div.keyword:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(keywordTemplate);
                        target.append(last);

                        initKeyword(last);
                    }

                    $('input', last).focus();
                }
            });

            //INITIALIZE
            $('div.keyword', target).each(function() {
                initKeyword($(this));
            });
        });

        /**
         * Meta Field
         */
        $(window).on('meta-field-init', function(e, target) {
            target = $(target);

            //TEMPLATES
            var metaTemplate ='<div class="meta">'
                + '<input type="text" class="meta-input key" /> '
                + '<input type="text" class="meta-input value" /> '
                + '<input type="hidden" name="post_tags[{{@key}}]" value=""/> '
                + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times"></i></a>'
                + '</div>';


            var addRemove = function(filter) {
                $('a.remove', filter).click(function() {
                    var val = $('input', filter).val();

                    $(this).parent().remove();
                });
            };

            //INITITALIZERS
            var initTag = function(filter) {
                addRemove(filter);

                $('.meta-input.key', filter).blur(function() {
                    var hidden = $(this).parent().find('input[type="hidden"]');

                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        $(hidden).attr('name', '');
                        return;
                    }

                    $(hidden).attr('name', $(target).data('name') + '[' + $(this).val() +']');
                });

                $('.meta-input.value', filter).blur(function() {
                    var hidden = $(this).parent().find('input[type="hidden"]');

                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        $(hidden).attr('name', '');
                        return;
                    }

                    $(hidden).attr('value', $(this).val());
                });
            };

            //append meta template
            $('.add-meta').click(function() {
                var last = $('div.meta:last', target);
                if(!last.length || $('input', last).val()) {
                    target.append(metaTemplate);
                    initTag(target);
                }

                return false;
            });

            //INITIALIZE
            $('div.meta', target).each(function() {
                initTag($(this));
            });
        });

        /**
         * WYSIWYG
         */
        $(window).on('wysiwyg-init', function (e, target) {
            $(target).wysihtml5({
                "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                "emphasis": true, //Italics, bold, etc. Default true
                "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                "html": true, //Button which allows you to edit the generated HTML. Default false
                "link": false, //Button to insert a link. Default true
                "image": false, //Button to insert an image. Default true,
                "color": false, //Button to change color of font
                "blockquote": false, //Blockquote
                toolbar: {
                    "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                    "emphasis": true, //Italics, bold, etc. Default true
                    "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                    "html": true, //Button which allows you to edit the generated HTML. Default false
                    "link": false, //Button to insert a link. Default true
                    "image": false, //Button to insert an image. Default true,
                    "color": false, //Button to change color of font
                    "blockquote": false, //Blockquote
                },
            });
        });

        /**
         * HTML config
         * data-do="file-field"
         * data-name="profile_file"
         */
        $(window).on('file-field-init', function(e, target) {
            //current
            var container = $(target);

            //get meta data

            //for hidden fields
            var name = container.attr('data-name');

            //make a file
            var file = $('input[type="file"]', target);
            var multiple = file.attr('multiple') || false;

            //listen for clicks
            container.click(function(e) {
                if(e.target !== file[0]) {
                    file.click();
                }
            });

            var generate = function(file, name) {
                var reader = new FileReader();

                reader.addEventListener('load', function () {
                    //create img and input tags
                    $('<input type="hidden" />')
                        .attr('name', name)
                        .val(reader.result)
                        .appendTo(target);
                }, false);

                if (file) {
                    reader.readAsDataURL(file);
                }
            };

            file.change(function() {
                if(!this.files || !this.files[0]) {
                    return;
                }

                //remove all
                $('input[type="hidden"]', target).remove();

                for(var path, i = 0; i < this.files.length; i++) {
                    //make a path
                    path = '';

                    if(multiple) {
                        path = '[' + i + ']';
                    }

                    path = name + path;

                    generate(this.files[i], path);
                }
            });
        });

        /**
         * Image Field
         * HTML config for single images
         * data-do="image-field"
         * data-name="profile_image"
         * data-width="200"
         * data-height="200"
         * data-alt="Change this Photo"
         *
         * HTML config for multiple images
         * data-do="image-field"
         * data-name="profile_image"
         * data-width="200"
         * data-height="200"
         * data-multiple="1"
         * data-alt="Change this Photo"
         *
         * HTML config for single images / multiple sizes
         * data-do="image-field"
         * data-name="profile_image"
         * data-width="0|200|100"
         * data-height="0|200|100"
         * data-label="original|small|large"
         * data-display="large|small"
         * data-alt="Change this Photo"
         *
         * HTML config for multiple images / multiple sizes
         * data-do="image-field"
         * data-name="profile_image"
         * data-width="0|200|100"
         * data-height="0|200|100"
         * data-label="original|small|large"
         * data-display="large"
         * data-multiple="1"
         * data-alt="Change this Photo"
         */
        $(window).on('image-field-init', function(e, target) {
            //current
            var container = $(target);

            //get meta data

            //for hidden fields
            var name = container.attr('data-name');

            //for file field
            var multiple = container.attr('data-multiple');

            //for image fields
            var alt = container.attr('data-alt');
            var classes = container.attr('data-class');

            var width = parseInt(container.attr('data-width') || 0);
            var height = parseInt(container.attr('data-height') || 0);

            var widths = container.attr('data-width') || '0';
            var heights = container.attr('data-height') || '0';
            var labels = container.attr('data-label') || '';
            var displays = container.attr('data-display') || '';

            widths = widths.split('|');
            heights = heights.split('|');
            labels = labels.split('|');
            displays = displays.split('|');

            if(!displays[0].length) {
                displays = false;
            }

            if(widths.length !== heights.length) {
                throw 'Invalid Attributes. Width and Height counts are not the same.';
            }

            //make an image config
            var config = [];
            widths.forEach(function(width, i) {
                var label = labels[i] || '' + i;

                if(widths.length === 1
                    && (
                        typeof labels[i] === 'undefined'
                        || !labels[i].length
                    )
                )
                {
                    label = false;
                }

                config.push({
                    label: label,
                    display: !displays || displays.indexOf(label) !== -1,
                    width: parseInt(widths[i]),
                    height: parseInt(heights[i])
                });
            });

            //make a file
            var file = $('<input type="file" />')
                .attr('accept', 'image/png,image/jpg,image/jpeg,image/gif')
                .addClass('hide')
                .appendTo(target);

            if(multiple) {
                file.attr('multiple', 'multiple');
            }

            //listen for clicks
            container.click(function(e) {
                if(e.target !== file[0]) {
                    file.click();
                }
            });

            var generate = function(file, name, width, height, display) {
                var image = new Image();

                //listen for when the src is set
                image.onload = function() {
                    //if no dimensions, get the natural dimensions
                    width = width || this.width;
                    height = height || this.height;

                    //so we can crop
                    $.cropper(file, width, height, function(data) {
                        //create img and input tags
                        $('<input type="hidden" />')
                            .attr('name', name)
                            .val(data)
                            .appendTo(target);

                        if(display) {
                            $('<img />')
                                .addClass(classes)
                                .attr('alt', alt)
                                .attr('src', data)
                                .appendTo(target);
                        }
                    });
                };

                image.src = URL.createObjectURL(file);
            };

            file.change(function() {
                if(!this.files || !this.files[0]) {
                    return;
                }

                //remove all
                $('input[type="hidden"], img', target).remove();

                for(var i = 0; i < this.files.length; i++) {
                    config.forEach(function(file, meta) {
                        //expecting
                        //  meta[label]
                        //  meta[display]
                        //  meta[width]
                        //  meta[height]

                        //make a path
                        var path = '';

                        if(meta.label !== false) {
                            path = '[' + meta.label + ']';
                        }

                        if(multiple) {
                            path = '[' + i + ']' + path;
                        }

                        path = name + path;

                        generate(
                            file,
                            path,
                            meta.width,
                            meta.height,
                            meta.display
                        );
                    }.bind(null, this.files[i]));
                }
            });
        });

        /**
         * Direct CDN Upload
         */
        $(window).on('cdn-upload-submit', function(e, target) {
            //setup cdn configuration
            var container = $(target);
            var config = { form: {}, inputs: {} };

            //though we upload this with s3 you may be using cloudfront
            config.cdn = container.attr('data-cdn');
            config.progress = container.attr('data-progress');
            config.complete = container.attr('data-complete');

            //form configuration
            config.form['enctype'] = container.attr('data-enctype');
            config.form['method'] = container.attr('data-method');
            config.form['action'] = container.attr('data-action');

            //inputs configuration
            config.inputs['acl'] = container.attr('data-acl');
            config.inputs['key'] = container.attr('data-key');
            config.inputs['X-Amz-Credential'] = container.attr('data-credential');
            config.inputs['X-Amz-Algorithm'] = container.attr('data-algorythm');
            config.inputs['X-Amz-Date'] = container.attr('data-date');
            config.inputs['Policy'] = container.attr('data-policy');
            config.inputs['X-Amz-Signature'] = container.attr('data-signature');

            var id = 0,
                // /upload/123abc for example
                prefix = config.inputs.key,
                //the total of files to be uploaded
                total = 0,
                //the amount of uploads complete
                completed = 0;

            //hiddens will have base 64
            $('input[type="hidden"]', target).each(function() {
                var hidden = $(this);
                var data = hidden.val();
                //check for base 64
                if(data.indexOf(';base64,') === -1) {
                    return;
                }

                //parse out the base 64 so we can make a file
                var base64 = data.split(';base64,');
                var mime = base64[0].split(':')[1];

                var extension = mimeExtensions[mime] || 'unknown';
                //this is what hidden will be assigned to when it's uploaded
                var path = prefix + (++id) + '.' + extension;

                //EPIC: Base64 to File Object
                var byteCharacters = window.atob(base64[1]);
                var byteArrays = [];

                for (var offset = 0; offset < byteCharacters.length; offset += 512) {
                    var slice = byteCharacters.slice(offset, offset + 512);

                    var byteNumbers = new Array(slice.length);

                    for (var i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }

                    var byteArray = new Uint8Array(byteNumbers);

                    byteArrays.push(byteArray);
                }

                var file = new File(byteArrays, {type: mime});

                //This Code is to verify that we are
                //encoding the file data correctly
                //see: http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
                //var reader  = new FileReader();
                //var preview = $('<img>').appendTo(target)[0];
                //reader.addEventListener("load", function () {
                //    preview.src = reader.result;
                //}, false);
                //reader.readAsDataURL(file);
                //return;

                //add on to the total
                total ++;

                //prepare the S3 form to upload just this file
                var form = new FormData();
                for(var name in config.inputs) {
                    if(name === 'key') {
                        form.append('key', path);
                        continue;
                    }

                    form.append(name, config.inputs[name]);
                }

                //lastly add this file object
                form.append('file', file);

                // Need to use jquery ajax
                // so that auth can catch
                // up request, and append access
                // token into it
                $.ajax({
                    url: config.form.action,
                    type: config.form.method,
                    // form data
                    data: form,
                    // disable cache
                    cache: false,
                    // do not set content type
                    contentType: false,
                    // do not proccess data
                    processData: false,
                    // on error
                    error: function(xhr, status, message) {
                        notifier.fadeOut('fast', function() {
                            notifier.remove();
                        });

                        $.notify(message, 'danger');
                    },
                    // on success
                    success : function() {
                        //now we can reassign hidden value from
                        //base64 to CDN Link
                        hidden.val(config.cdn + '/' + path);

                        //if there is more to upload
                        if ((++completed) < total) {
                            //update bar
                            var percent = Math.floor((completed / total) * 100);
                            bar.css('width', percent + '%').html(percent + '%');

                            //do nothing else
                            return;
                        }

                        notifier.fadeOut('fast', function() {
                            notifier.remove();
                        });

                        $.notify(config.complete, 'success');

                        //all hidden fields that could have possibly
                        //been converted has been converted
                        //submit the form
                        target.submit();
                    }
                });
            });

            //if there is nothing to upload
            if(!total) {
                //let the form submit as normal
                return;
            }

            //otherwise we are uploading something, so we need to wait
            e.preventDefault();

            var message = '<div>' + config.progress + '</div>';
            var progress = '<div class="progress"><div class="progress-bar"'
            + 'role="progressbar" aria-valuenow="2" aria-valuemin="0"'
            + 'aria-valuemax="100" style="min-width: 2em; width: 0%;">0%</div></div>';

            var notifier = $.notify(message + progress, 'info', 0);
            var bar = $('div.progress-bar', notifier);
        });

        var mimeExtensions = {
            'application/mathml+xml': 'mathml',
            'application/msword': 'doc',
            'application/oda': 'oda',
            'application/ogg': 'ogg',
            'application/pdf': 'pdf',
            'application/rdf+xml': 'rdf',
            'application/vnd.mif': 'mif',
            'application/vnd.mozilla.xul+xml': 'xul',
            'application/vnd.ms-excel': 'xls',
            'application/vnd.ms-powerpoint': 'ppt',
            'application/vnd.rn-realmedia': 'rm',
            'application/vnd.wap.wbxml': 'wbmxl',
            'application/vnd.wap.wmlc': 'wmlc',
            'application/vnd.wap.wmlscriptc': 'wmlsc',
            'application/voicexml+xml': 'vxml',
            'application/x-javascript': 'js',
            'application/x-shockwave-flash': 'swf',
            'application/x-tar': 'tar',
            'application/xhtml+xml': 'xhtml',
            'application/xml': 'xml',
            'application/xml-dtd': 'dtd',
            'application/xslt+xml': 'xslt',
            'application/zip': 'zip',
            'audio/basic': 'snd',
            'audio/midi': 'midi',
            'audio/mp4a-latm': 'm4p',
            'audio/mpeg': 'mpga',
            'audio/x-aiff': 'aiff',
            'audio/x-mpegurl': 'm3u',
            'audio/x-pn-realaudio': 'ram',
            'audio/x-wav': 'wav',
            'image/bmp': 'bmp',
            'image/cgm': 'cgm',
            'image/gif': 'gif',
            'image/ief': 'ief',
            'image/jp2': 'jp2',
            'image/jpg': 'jpg',
            'image/jpeg': 'jpg',
            'image/pict': 'pict',
            'image/png': 'png',
            'image/svg+xml': 'svg',
            'image/tiff': 'tiff',
            'image/vnd.djvu': 'djvu',
            'image/vnd.wap.wbmp': 'wbmp',
            'image/x-cmu-raster': 'ras',
            'image/x-icon': 'ico',
            'image/x-macpaint': 'pntg',
            'image/x-portable-anymap': 'pnm',
            'image/x-portable-bitmap': 'pbm',
            'image/x-portable-graymap': 'pgm',
            'image/x-portable-pixmap': 'ppm',
            'image/x-quicktime': 'qtif',
            'image/x-rgb': 'rgb',
            'image/x-xbitmap': 'xbm',
            'image/x-xpixmap': 'xpm',
            'image/x-xwindowdump': 'xwd',
            'model/iges': 'igs',
            'model/mesh': 'silo',
            'model/vrml': 'wrl',
            'text/calendar': 'ifb',
            'text/css': 'css',
            'text/html': 'html',
            'text/plain': 'txt',
            'text/richtext': 'rtx',
            'text/rtf': 'rtf',
            'text/sgml': 'sgml',
            'text/tab-separated-values': 'tsv',
            'text/vnd.wap.wml': 'wml',
            'text/vnd.wap.wmlscript': 'wmls',
            'text/x-setext': 'etx',
            'video/mp4': 'mp4',
            'video/mpeg': 'mpg',
            'video/quicktime': 'qt',
            'video/vnd.mpegurl': 'mxu',
            'video/x-dv': 'dv',
            'video/x-m4v': 'm4v',
            'video/x-msvideo': 'avi',
            'video/x-sgi-movie': 'movie'
        };
    })();

    /**
     * Notifier
     */
    (function() {
        $(window).on('notify-init', function(e, trigger) {
            var timeout = parseInt($(trigger).attr('data-timeout') || 3000);

            if(!timeout) {
                return;
            }

            setTimeout(function() {
                $(trigger).fadeOut('fast', function() {
                    $(trigger).remove();
                });
            }, timeout);
        });

        $.extend({
            notify: function(message, type, timeout) {
                type = type || 'info';

                if(typeof timeout === 'undefined') {
                    timeout = 3000;
                }

                var template = '<div data-do="notify" data-timeout="{TIMEOUT}" class="notify notify-{TYPE}"><span class="message">{MESSAGE}</span></div>';

                var notification = $(template
                    .replace('{TYPE}', type)
                    .replace('{MESSAGE}', message)
                    .replace('{TIMEOUT}', timeout));

                $(document.body).append(notification);
                return notification.doon();
            }
        })
    })();

    /**
     * Toastr
     */
    (function() {
        // toaster options
        toastr.options = {
            "closeButton": false,
            "preventDuplicates": true,
            "positionClass": "toast-bottom-right"
        };

        $(window).on('toastr-init', function(e, target) {
                var element = $(target),
                text = element.text(),
                type = element.attr('data-type');

            // start toastr
            toastr[type](text);
        });
    })();

    /**
     * Check All Checkboxes function
     */
    (function() {
        $('#checkAll').on('click', function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    })();

    /**
     * Deals Functions
     */
    (function() {
        /**
         * Change Deal Amount
         */
    $(window).on('deal-amount-change', function (e, target) {
        var amount = $(target).val();
        console.log(amount)
        amount = Number(amount);
        amount = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        $('.deal-amount-display').html(amount);
        return false;
    });

    });

    /**
     * Get Template Detail
     */
    $(window).on('pipeline-select-change', function (e, target) {
        var id = $(target).val();
        $('.select-stage')
            .attr('class', 'form-control select-stage hide')
            .removeAttr('name');
        $('#stage-'+id).removeClass('hide').attr('name', 'deal_status');
    });

    /**
     * Get Template Detail
     */
    $(window).on('pipeline-select-init', function (e, target) {
        var id = $(target).val();
        $('.select-stage')
            .attr('class', 'form-control select-stage hide')
            .removeAttr('name');
        $('#stage-'+id).removeClass('hide').attr('name', 'deal_status');
    });

    /**
     * Select Agent
     */
    $(window).on('select-agent-keyup', function (e, target) {
        var keyword = $(target).val();
        var field =  $(target).data('field');
        $(target).parent().parent().find('.auto-suggest').html('');
        $.get('/ajax/sales/agent/search?q='+keyword, function (response) {
            response = response.results;
            $(target).parent().parent().find('.auto-suggest').removeClass('hide');
            for(var i in response.rows) {
                var temp = '<li><a class="auto-suggest-item"'
                    + 'data-field="'+field+'"'
                    + 'data-placeholder="select-agent"'
                    + 'data-id="' + response.rows[i].profile_id +'">'
                    + response.rows[i].profile_name
                    + '</a></li>';
                $(target).parent().parent().find('.auto-suggest').append(temp);
            }
        });
    });

    /**
     * Add Attachment
     */
    $(window).on('add-attachment-click', function (e, target) {
        var elem = $(target).data('target');
        $(elem).trigger('click');
    });

    $(window).on('add-attachment-name-change', function (e, target) {
        var files = $(target).get(0).files;
        if (files && files.length > 1) {
			fileName = files.length + ' files selected';

        } else {
			fileName = e.target.value.split( '\\' ).pop();
        }

        $(target).parent().find('.attachments').html(fileName);
    });

    $('.auto-suggest').on('click', '.auto-suggest-item', function() {
        var id = $(this).data('id');
        var name = $(this).text();
        var field = $(this).data('field');
        var placeholder = $(this).data('placeholder');
        $('input[name="'+field+'"]').val(id);
        $('#'+placeholder).val(name);
        $('.auto-suggest').addClass('hide');
    });

    $(document).on('blur', '.select-keyup', function() {
        setTimeout(function() {
            $('.auto-suggest').addClass('hide');
        }, 200);
    });

    $(window).on('deal-amount-change', function (e, target) {
        var amount = $(target).val().replace(/,/g, '');
        amount = Number(amount);

        var id = $(target).data('id');

        var data = {
            'deal_amount' : amount,
            'deal_id' : id
        };

        $.post('/ajax/sales/deal/update', data, function(response) {
            response = JSON.parse(response);

            // Checks for errors
            if (response.error) {
                toastr.error(response.message);
                return;
            }

            toastr.success(response.message);
            console.log(response);
            return;
        });

        return false;
    });

    $(window).on('deal-close-change', function (e, target) {
        var date = $(target).val();
        var id = $(target).data('id');

        var data = {
            'deal_close' : date,
            'deal_id' : id
        };

        $.post('/ajax/sales/deal/update', data, function(response) {
            response = JSON.parse(response);

            // Checks for errors
            if (response.error) {
                toastr.error(response.message);
                return;
            }

            toastr.success(response.message);
            console.log(response);
            return;
        });
    });

    $(window).on('deal-status-change', function (e, target) {
        var status = $(target).val();
        var id = $(target).data('id');
        var current = $(target).data('current');
        var name = $(target).data('name');

        var data = {
            'deal_status' : status,
            'deal_id' : id,
        };

        $.post('/ajax/sales/deal/update/status', data, function(response) {
            response = JSON.parse(response);

            // Checks for errors
            if (response.error) {
                toastr.error(response.message);
                return;
            }

            toastr.success(response.message);
            console.log(response);
            return;
        });
    });

    $(window).on('download-click', function (e, target) {
        file = $(target).data('file');
        mime = $(target).data('mime');
        name = $(target).data('name');
        if (file && name && mime) {
            download(file, name, mime);
        }
    });

    $(window).on('event-create-submit', function (e, target) {
        e.preventDefault();
        var data = {
            'event_title' : $('input[name="event_title"]').val(),
            'event_type': $('select[name="event_type"]').val(),
            'event_location' : $('input[name="event_location"]').val(),
            'event_start': $('input[name="event_start"]').val(),
            'event_end': $('input[name="event_end"]').val(),
            'event_details': $('#event_details').val(),
            'deal_id': $('input[name="deal_id"]').val()
        }

        $.post('/ajax/sales/event/create', data)
        .done(function(response) {
            response = JSON.parse(response);

            // Checks for errors
            if (response.error) {
                toastr.error(response.message);

                if (response.validation) {
                    for (i in response.validation) {
                        toastr.error(response.validation[i]);
                    }
                }

                return;
            }

            toastr.success(response.message);
            location.reload();
            return;
        });
    });

    /**
     * Board AJAX Pagination
     */
    $(window).on('board-ajax-init', function(e, target) {
        //figure out what page to go to (start)
        var start = 0,
            startSponsored = 0,
            stage = $(target).data('id');
            // console.log(target);
        if(window.location.search.indexOf('start=') !== -1) {
            //remove the ?
            window.location.search.substr(1).split('&').forEach(function(query) {
                query = query.split('=');
                if(typeof query[0] !== 'undefined'
                && typeof query[1] !== 'undefined'
                && query[0] === 'start'
                && parseInt(query[1])) {
                    start = parseInt(query[1]);
                }
            });
        }

        var paginating = false, loader = $(target).next(), paginator = loader.next();

        $(target).scroll(function() {
            //if we are already paginating
            if(paginating) {
                return;
            }

            var variableHeight = $(this).scrollTop() + $(window).height();
            var totalHeight = $(document.body).height();
            var percent = variableHeight / totalHeight;
            var range = 50;
            if(percent < .75) {
                return;
            }

            paginating = true;
            start += range;
            var search = window.location.search;

            if (search.indexOf('start=') !== -1) {
                search = search.replace(/start\=[0-9]+/ig, 'start=' + start);
            } else if (search.indexOf('?') !== -1) {
                search += '&start=' + start;
            } else {
                search = '?start=' + start;
            }

            // show ajax loader
            loader.removeClass('hide');

            $.get('/control/business/pipeline/1/board' + search, function(response) {
                response = $(response);
                if(!$('.draggable-wrapper[data-id="'+stage+'"] ul', response).length) {
                    loader.addClass('hide');
                    return;
                }
                
                // append the results
                $(target).find('ul').append(response.find('.draggable-wrapper[data-id="'+stage+'"] ul').html());

                // hide ajax loader
                loader.addClass('hide');

                $(document.body).doon();

                $('ul.loader-pagination li.active').removeClass('active').next().addClass('active');

                paginating = false;
            });
        });
    });

    /**
     * activity timeline AJAX Pagination
     */
    $(window).on('activity-ajax-init', function(e, target) {
        console.log(target);
        //figure out what page to go to (start)
        var start = 0,
            startSponsored = 0;
            // console.log(target);
        if(window.location.search.indexOf('start=') !== -1) {
            //remove the ?
            window.location.search.substr(1).split('&').forEach(function(query) {
                query = query.split('=');
                if(typeof query[0] !== 'undefined'
                && typeof query[1] !== 'undefined'
                && query[0] === 'start'
                && parseInt(query[1])) {
                    start = parseInt(query[1]);
                }
            });
        }

        var paginating = false, loader = $(target).next(), paginator = loader.next();

        $(target).scroll(function() {
            //if we are already paginating
            if(paginating) {
                return;
            }

            var variableHeight = $(this).scrollTop() + $(window).height();
            var totalHeight = $(document.body).height();
            var percent = variableHeight / totalHeight;
            var range = 6;
            if(percent < .75) {
                return;
            }

            paginating = true;
            start += range;
            var search = window.location.search;

            if (search.indexOf('start=') !== -1) {
                search = search.replace(/start\=[0-9]+/ig, 'start=' + start);
            } else if (search.indexOf('?') !== -1) {
                search += '&start=' + start;
            } else {
                search = '?start=' + start;
            }

            // show ajax loader
            loader.removeClass('hide');
            console.log(search);
            $.get('/control/business/activity/timeline' + search, function(response) {
                response = $(response);
                if(!$('.activity', response).length) {
                    loader.addClass('hide');
                    return;
                }

                // append the results
                $(target).append(response.find('.timeline').html());
                var height = ($(target).height() * (start/range+1.8));
                $('style#dynamic-style').html('.timeline::after { height: ' +
                    height + 'px; }');
                $('.timeline::after').css('height', height+'px');

                // hide ajax loader
                loader.addClass('hide');

                $(document.body).doon();

                $('ul.loader-pagination li.active').removeClass('active').next().addClass('active');

                paginating = false;
            });

        });
    });

    //activate all scripts
    $(document.body).doon();
    $('.datetimepicker').datetimepicker();
});
