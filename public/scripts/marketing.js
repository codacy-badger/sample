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
         * Package Field
         */
        $(window).on('package-field-init', function(e, target) {
            //translations
            try {
                var translations = JSON.parse($('#package-translations').html());
            } catch(e) {
                var translations = {};
            }

            [
                'Package'
            ].forEach(function(translation) {
                translations[translation] = translations[translation] || translation;
            });

            target = $(target);

            //TEMPLATES
            var packageTemplate = '<div class="package"><input type="text" class="package-input'
            + ' text-field" name="profile_package[]" placeholder="'
            + translations['Package']+'" value="" />'
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
            var initPackage = function(filter) {
                addRemove(filter);
                addResize(filter);

                $('input', filter).blur(function() {
                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        //remove it
                        $(this).next().click();
                    }

                    var count = 0;
                    var currentPackageValue = $(this).val();
                    $('div.package input', target).each(function() {
                        if(currentPackageValue === $(this).val()) {
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
                if($(e.target).hasClass('package-field')) {
                    var last = $('div.package:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(packageTemplate);
                        target.append(last);

                        initPackage(last);
                    }

                    $('input', last).focus();
                }
            });

            //INITIALIZE
            $('div.package', target).each(function() {
                initPackage($(this));
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

        /**
         * Create Action - Add Condition
         * data-do="add-condition"
         */
         $(window).on('add-condition-init', function (e, target) {
             var template = $('#condition-template').html();
             var ctr = $('#when .input-range-when').length;
             template = template.replace(/\[i\]/g, ctr, template);
             $('#when').append(template);
         });

        $(window).on('add-condition-click', function (e, target) {
            var template = $('#condition-template').html();
            var ctr = $('#when .input-range-when').length;
            template = template.replace(/\[i\]/g, ctr, template);
            $('#when').append(template);
        });

        $(document).on('click', '.remove-condition', function () {
            $(this).parent().parent().remove();
            var ctr = $('#when .input-range-when').length;
        });

        $(document).on('change', '.condition-select', function () {
            var current = $(this).val();
            var id = $(this).parent().parent().data('id');

            var def = '<input class="form-control" name="action_when[' + id +
                '][value]" placeholder="10000" type="text" />';

            $('.input-range-when[data-id="'+id+'"] .condition-value')
                .html(def);

            var select = $('<select class="form-control" name="action_when['
                + id + '][value]" />');

            // if birthdate is selected convert the value input box to date
            if (current == 'profile_birth') {
                $('[name="action_when['+id+'][operator]"]').val('LIKE');
                $('[name="action_when['+id+'][value]"]').attr('type', 'date');
            }

            // replace value input box to select type
            if (current == 'profile_gender') {
                $('[name="action_when['+id+'][operator]"]').val('==');
                $("<option />", {value: 'male', text: 'Male'}).appendTo(select);
                $("<option />", {value: 'female', text: 'Female'}).appendTo(select);

                $('.input-range-when[data-id="'+id+'"] .condition-value').html(select);
            }

            if (current == 'profile_type') {
                $('[name="action_when['+id+'][operator]"]').val('==');
                $("<option />", {value: 'poster', text: 'Poster'}).appendTo(select);
                $("<option />", {value: 'seeker', text: 'Seeker'}).appendTo(select);

                $('.input-range-when[data-id="'+id+'"] .condition-value').html(select);
            }

            if (current == 'profile_verified') {
                $('[name="action_when['+id+'][operator]"]').val('==');
                $("<option />", {value: 1, text: 'Verified Company'}).appendTo(select);
                $("<option />", {value: 2, text: 'Verified Recruiter'}).appendTo(select);

                $('.input-range-when[data-id="'+id+'"] .condition-value').html(select);
            }

            if (current == 'profile_subscribe') {
                $('[name="action_when['+id+'][operator]"]').val('==');
                $("<option />", {value: 1, text: 'Yes'}).appendTo(select);
                $("<option />", {value: 0, text: 'No'}).appendTo(select);

                $('.input-range-when[data-id="'+id+'"] .condition-value').html(select);
            }

            if (current == 'profile_tags') {
                $('[name="action_when['+id+'][operator]"]').val('HAS');
            }

            if (current == 'profile_story') {
                $('[name="action_when['+id+'][operator]"]').val('HAS');
            }

            if (current == 'profile_achievements') {
                $('[name="action_when['+id+'][operator]"]').val('HAS');
                $("<option />", {value: 'signup', text: 'Signup Badge'}).appendTo(select);
                $("<option />", {value: 'post_1', text: '1st Post Badge'}).appendTo(select);
                $("<option />", {value: 'post_10', text: '10th Post Badge'}).appendTo(select);
                $("<option />", {value: 'post_50', text: '50th Post Badge'}).appendTo(select);
                $("<option />", {value: 'post_100', text: '100th Post Badge'}).appendTo(select);
                $("<option />", {value: 'interested_1', text: '1st Interested Badge'}).appendTo(select);
                $("<option />", {value: 'interested_10', text: '10th Interested Badge'}).appendTo(select);
                $("<option />", {value: 'interested_50', text: '50th Interested Badge'}).appendTo(select);
                $("<option />", {value: 'interested_100', text: '100th Interested Badge'}).appendTo(select);
                $("<option />", {value: 'promoted_10', text: '10th Promoted Badge'}).appendTo(select);
                $("<option />", {value: 'promoted_50', text: '50th Promoted Badge'}).appendTo(select);
                $("<option />", {value: 'promoted_100', text: '100th Promoted Badge'}).appendTo(select);
                $("<option />", {value: 'downloaded_1', text: '1st Downloaded Resume Badge'}).appendTo(select);
                $("<option />", {value: 'downloaded_10', text: '10th Downloaded Resume Badge'}).appendTo(select);
                $("<option />", {value: 'downloaded_50', text: '50th Downloaded Resume Badge'}).appendTo(select);
                $("<option />", {value: 'downloaded_100', text: '100th Downloaded Resume Badge'}).appendTo(select);
                $("<option />", {value: 'verified_company', text: 'Verified Company Badge'}).appendTo(select);
                $("<option />", {value: 'verified_recruiter', text: 'Verified Recruiter Badge'}).appendTo(select);
                $("<option />", {value: 'medal_honor', text: 'Jobayan Medal of Honor Badge'}).appendTo(select);

                $('.input-range-when[data-id="'+id+'"] .condition-value').html(select);
            }
        });

        /**
         * Get Template Detail
         */
        $(window).on('template-preview-change', function (e, target) {
            var container = $(target);
            var templateId = container.val();

            $.ajax({
                url: '/ajax/control/marketing/template/detail/' + templateId,
                type: 'GET',
                // disable cache
                cache: false,
                // on error
                error: function (xhr, status, message) {
                    //remove loader
                    $('div.preview-loader').addClass('hide');

                    $.notify(message, 'danger');
                },
                beforeSend: function () {
                    $('div.preview-loader').removeClass('hide');
                },
                // on success
                success: function (response) {
                    //remove loader
                    $('div.preview-loader').addClass('hide');

                    var data = response;

                    //there was an error with the request
                    if (data.error) {
                        //error
                        $.notify(config.complete, 'error');
                        return false;
                    }

                    //replace email preview template
                    $('.template-preview-container div').replaceWith('<div>' + data.results.template_html + '</div>');
                    return false;
                }
            });

            //otherwise we are uploading something, so we need to wait
            e.preventDefault();
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
     * Wall UI
     */
    (function() {
        /**
         * Post Search AJAX Pagination
         */
        $(window).on('post-search-ajax-init', function (e, target) {
            //figure out what page to go to (start)
            var start = 0;
            if (window.location.search.indexOf('start=') !== -1) {
                //remove the ?
                window.location.search.substr(1).split('&').forEach(function (query) {
                    query = query.split('=');
                    if (typeof query[0] !== 'undefined'
                        && typeof query[1] !== 'undefined'
                        && query[0] === 'start'
                        && parseInt(query[1])) {
                        start = parseInt(query[1]);
                    }
                });
            }

            var paginating = false, loader = $(target).next(), paginator = loader.next();

            $(window).scroll(function () {
                //if we are already paginating
                if (paginating) {
                    return;
                }

                // trigger lazy load if no image src
                [].forEach.call(document.querySelectorAll('img'), function (img) {
                    if (!img.getAttribute('src')) {
                        $("img.lazy").myLazyLoad();
                    }
                });

                var variableHeight = $(this).scrollTop() + $(window).height();
                var totalHeight = $(document.body).height();
                var percent = variableHeight / totalHeight;
                var range = 50;

                if (percent < .75) {
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

                if (window.location.href.indexOf('/profile-post') !== -1) {
                    var profileId = window.location.href.split('/');
                    profileId.pop();
                    profileId = profileId.pop().split('-').pop().substr(1);

                    search += '&profile=' + profileId;
                }

                // show ajax loader
                loader.removeClass('hide');

                $.get('/ajax/post/search' + search, function (response) {
                    response = $(response);

                    if (!$('div', response).length) {
                        loader.addClass('hide');
                        return;
                    }

                    $(target).append(response);

                    // hide ajax loader
                    loader.addClass('hide');

                    response.doon();

                    $('ul.loader-pagination li.active').removeClass('active').next().addClass('active');

                    paginating = false;
                });
            });
        });
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
     * Pull template
     */
    (function() {
        $(window).on('pull-template-init', function (e, target) {
            var template = $('select[name="template_id"]').val();
            var medium = $('select[name="*_medium"]').val();

            $('div#preview-loader').removeClass('hide');

            $.get('/ajax/control/marketing/template/detail/' + template, function (response) {
                $('div#preview-loader').addClass('hide');
                if (!response.error) {
                    if (medium == 'sms') {
                        var preview = $("<div>").html(response.results.template_text);
                    } else {
                        var preview = $("<div>").html(response.results.template_html);
                    }

                    $('#preview').html(preview);
                }
            }, 'json');
        });

        $(window).on('pull-template-change', function (e, target) {
            var template = $('select[name="template_id"]').val();
            var medium = $('select[name="campaign_medium"]').val();
            $('div#preview-loader').removeClass('hide');

            $.get('/ajax/control/marketing/template/detail/' + template, function (response) {
                $('div#preview-loader').addClass('hide');
                if (!response.error) {
                    if (medium == 'sms') {
                        var preview = $("<div>").html(response.results.template_text);
                    } else {
                        var preview = $("<div>").html(response.results.template_html);
                    }

                    $('#preview').html(preview);
                }
            }, 'json');
        });
    })();

    //activate all scripts
    $(document.body).doon();
});
