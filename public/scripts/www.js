jQuery(function($) {
    triggerSuggestion = function(e) {
        $(window).trigger('suggestion-field-init', [e]);
    }

    // global function for form serialization
    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();

        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });

        return o;
    };

    /**
     * Featured Change Select
     */
    $(window).on('featured-select-change', function(e, target) {
        window.location.href = $(target).val();
        $(target).val(0);
    });

    /**
     * Search
     */
    (function() {
        $(window).on('add-keyword-click', function(e, target) {
            var search = $('.tag-field');
            var keyword = $(target).data('value');
            var tagTemplate = '<div class="tag"><input type="text" autocomplete="off" class="tag-input'
            + ' text-field" name="q[]" placeholder="Keyword" onkeyup="triggerSuggestion(this)" value="'+keyword+'"/>'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times"></i></a></div>';

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

                    if((width + 40) > search.width()) {
                        width = search.width() - 40;
                    }

                    width = (width * 2) + 5;

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
            var elem = $(tagTemplate);
            $('.search-group .tag-field').append(elem);
            initTag(elem);
        });
    })();

    /**
     * Checkout
     */
    (function() {
        $(window).on('checkout-modal-click', function(e, target) {
            var modal = $('#checkout_modal'),
                name = $(target).parents('.checkout-body').find('input[name="name"]').val(),
                number = $(target).parents('.checkout-body').find('input[name="number"]').val(),
                expiry_month = $(target).parents('.checkout-body').find('input[name="exp_month"]').val(),
                expiry_year = $(target).parents('.checkout-body').find('input[name="exp_year"]').val(),
                cvc =  $(target).parents('.checkout-body').find('input[name="cvc"]').val(),
                last = number.substr(number.length - 4),
                card_number  = number.substr(0, number.length - 4).replace(/[0-9]/g, '&middot;') + last;

            // populate to modal
            $(modal).find('#card-name').html(name);
            $(modal).find('#card-number').html(card_number);
            $(modal).find('#exp-month').html(expiry_month);
            $(modal).find('#exp-year').html(expiry_year);
            $(modal).find('#card-cvc').html(cvc);

            // show modal
            modal.modal('show');
        });
    })();

    /**
     * Sign Up
     */
    (function() {
        $(window).on('make-active-init', function(e, target) {
            if ($('input[name="signup_type"]').val() != 'poster') {
                $('#company').addClass('hide');
                $('.option-seeker').addClass('active');
                $('.option-poster').removeClass('active');
            } else {
                $('.option-poster').addClass('active');
                $('.option-seeker').removeClass('active');
            }
        });

        $(window).on('make-active-click', function(e, target) {
            var active = $(target).data('id');
            $(target).parent().find('a').removeClass('active');
            $(target).addClass('active');

            $('#company').addClass('hide');
            if (active == 'poster') {
                $('#company').removeClass('hide');
            }

            // Changes the type of the profile
            $('input[name="signup_type"]').val(active);
        });
    })();

    /**
     * Post Submit
     */
    (function() {
        $(window).on('submit-post-click', function(e, target) {
            e.preventDefault();
            $('.submit').attr('disabled', true);

            var count = $(target).attr('data-count');

            if (count >= 5) {
                var creditPay = $('input[name="credit_pay"]').val();

                if(creditPay == 0) {
                    $('#credit_modal').modal('show');
                } else {
                    $('#post-form').submit();
                }
            } else {
                $('#post-form').submit();
            }
        });
    })();

    /**
     * Restore Posts
     */
    (function() {
        $(window).on('restore-post-click', function(e, target) {
            e.preventDefault();
            $('.submit').attr('disabled', true);

            var count = $(target).attr('data-count');
            var redirect = '/profile/post/restore/' + $(target).data('post-id');

            $('#credit_modal').attr('data-redirect', redirect);

            if (count >= 5) {
                var creditPay = $('input[name="credit_pay"]').val();

                if (creditPay == 0) {
                    $('#credit_modal').modal('show');
                }
            }
        });
    })();

    $(window).on('credit-pay-click', function(e, target) {

        $('input[name="credit_pay"]').val('1');
        var redirect = $('#credit_modal').data('redirect');

        if (typeof redirect !== 'undefined') {
            window.location.replace(redirect);
        } else {
            $('#post-form').submit();
        }
    });

    $('#credit_modal').on('hidden.bs.modal', function (e) {
        $('.submit').attr('disabled', false);
    });

    /**
     * General Forms
     */
    (function() {
        /**
         * Suggestion Field
         */
        $(window).on('suggestion-field-init', function(e, target) {
            target = $(target);

            //ATTRIBUTES
            var type = target.attr('data-type');
            var position = target.attr('data-position');
            var top = target.attr('data-top');
            var left = target.attr('data-left');
            var width = target.attr('data-width');

            var offsetTop = parseFloat(target.attr('data-offset-top')) || 0;
            var offsetLeft = parseFloat(target.attr('data-offset-left')) || 0;

            //TEMPLATES
            var listTemplate = '<div class="input-suggestion hide">'
                             + '<ul class="suggestion-list"></ul></div>';
            var itemTemplate = '<li class="suggestion-item">{VALUE}</li>';

            //SELECTORS
            var inputSuggestion = $(listTemplate).appendTo(document.body);
            var suggestionList = $('ul.suggestion-list', inputSuggestion);

            if(position) {
                inputSuggestion.css('position', position);
            }

            //REUSABLE
            var loadSuggestions = function(list, callback) {
                suggestionList.html('');

                list.forEach(function(item) {
                    item.label = htmlSpecialChars(item.label);

                    var row  = itemTemplate.replace('{VALUE}', item.label);

                    row = $(row).click(function() {
                        callback(item);
                        inputSuggestion.addClass('hide');
                    });

                    suggestionList.append(row);
                });

                if(list.length) {
                    inputSuggestion.removeClass('hide');
                    if(top) {
                        inputSuggestion.css('top', top + 'px');
                    } else {
                        inputSuggestion.css('top', (target.offset().top + target.height() + offsetTop) + 'px');
                    }

                    if(left) {
                        inputSuggestion.css('left',left + 'px');
                    } else {
                        inputSuggestion.css('left', (target.offset().left + offsetLeft) + 'px');
                    }

                    if(width) {
                        inputSuggestion.width(width);
                    } else {
                        inputSuggestion.width(
                            target.width()
                            + parseInt(target.css('padding-left').replace('px', ''))
                            + parseInt(target.css('padding-right').replace('px', ''))
                        );
                    }
                } else {
                    inputSuggestion.addClass('hide');
                }
            };

            var getSuggestions = function(filter, callback) {
                var searching = false, prevent = false;

                $(filter).keypress(function(e) {
                    if(e.keyCode == 13 && prevent) {
                        e.preventDefault();
                    }
                }).keydown(function(e) {
                    prevent = false;
                    switch(e.keyCode) {
                        case 9:
                        case 16:
                            return;
                    }

                    if(!inputSuggestion.hasClass('hide')) {
                        switch(e.keyCode) {
                            case 40: //down
                                var next = $('li.hover', inputSuggestion).removeClass('hover').index() + 1;
                                var unitlength = next * 31;

                                if(next === $('li', inputSuggestion).length) {
                                    next = 0;
                                }

                                $('li:eq('+next+')', inputSuggestion).addClass('hover');
                                $( ".suggestion-list" ).scrollTop(unitlength);
                                return;
                            case 38: //up
                                var prev = $('li.hover', inputSuggestion).removeClass('hover').index() - 1;
                                var unitlength = prev * 31;

                                if(prev < 0) {
                                    prev = $('li', inputSuggestion).length - 1;
                                }

                                $('li:eq('+prev+')', inputSuggestion).addClass('hover');
                                $( ".suggestion-list" ).scrollTop(unitlength);

                                return;
                            case 13: //enter
                                if($('li.hover', inputSuggestion).length) {
                                    $('li.hover', inputSuggestion)[0].click();
                                    prevent = true;
                                }
                                return;
                            case 37:
                            case 39:
                            case 9:
                                return;
                        }
                    }

                    if(searching) {
                        return;
                    }

                    setTimeout(function() {
                        if ($('input', filter).val() == '') return;

                        searching = true;
                        var url = '/rest/term/search';
                        var query = { q: $(filter).val() };
                        if(type) {
                            if (type == 'location') {
                                url = '/ajax/area/search';
                                query = {
                                    q: [$(filter).val()],
                                    filter: {
                                        area_type : 'city'
                                    }
                                };
                            } else if (type == 'position') {
                                url = '/ajax/position/search';
                                query = { q: [$(filter).val()] };
                            } else if (type == 'school') {
                                url = '/ajax/school/search';
                                query = { q: [$(filter).val()] };
                            } else if (type == 'degree') {
                                url = '/ajax/degree/search';
                                query = { q: [$(filter).val()] };
                            } else {
                                query.filter = { term_type: type };
                            }

                        }

                        $.ajax({
                            url : url,
                            type : 'GET',
                            data : query,
                            crossDomain : true,
                            success : function(response) {
                                if(response.error) {
                                    return;
                                }

                                var list = [];
                                if (type && type == 'location') {
                                    var rows = response.results;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].area_name,
                                                value : rows[i].area_name
                                            });
                                        }
                                    }
                                } else if (type && type == 'position') {
                                    var rows = response.results;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].position_name,
                                                value : rows[i].position_name
                                            });
                                        }
                                    }
                                } else if (type && type == 'school') {
                                    var rows = response.results;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].school_name,
                                                value : rows[i].school_name
                                            });
                                        }
                                    }
                                } else if (type && type == 'degree') {
                                    var rows = response.results;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].degree_name,
                                                value : rows[i].degree_name
                                            });
                                        }
                                    }
                                } else {
                                    var rows = response.results.rows;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].term_name,
                                                value : rows[i].term_name
                                            });
                                        }
                                    }
                                }

                                loadSuggestions(list, callback);
                                searching = false;
                            }, error : function() {
                                searching = false;
                            }
                        });
                    }, 1);
                });
            };

            var htmlSpecialChars = function(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };

                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            };

            //INITITALIZERS
            inputSuggestion.mouseover(function() {
                $('li', inputSuggestion).removeClass('hover');

                var stop = function(e) {
                    if ($(e.target).hasClass('form-control')
                        || $(e.target).hasClass('input-suggestion')
                        || $(e.target).hasClass('suggestion-list')
                        || $(e.target).hasClass('suggestion-item')
                    ) {
                         return;
                    }

                    inputSuggestion.addClass('hide');
                    $(document.body).unbind('mouseover', stop);
                    $(window).trigger('suggestion-mouseout');
                };

                $(document.body).on('mouseover', stop);
            });

            getSuggestions(target, function(item) {
                target.val(item.value).trigger('keyup');
            });

            // loses focus on post position field
            $('input[name="post_position"]').focusout(function(e) {
                var clickElement = function(e) {
                    var target = $(e.target);

                    if(target.is('li.suggestion-item') || target.is('input[name="post_position"]')) {
                        return;
                    }

                    // hide input suggestion
                    if(inputSuggestion.is(':visible')) {
                        inputSuggestion.addClass('hide');
                        return;
                    }
                };

                $(document.body).on('click', clickElement);
            });
        });

        /**
         * Tag Field
         */
        $(window).on('tag-field-init', function(e, target) {
            //translations
            try {
                var translations = JSON.parse($('#tag-translations').html());
            } catch(e) {
                var translations = {};
            }

            [
                'Tag'
            ].forEach(function(translation) {
                translations[translation] = translations[translation] || translation;
            });

            target = $(target);
            var placeholder = translations['Tag'];
            var remove = '<i class="fa fa-times"></i>';
            var suggest = '';

            if (target.data('suggest')) {
                suggest = ' onkeyup="triggerSuggestion(this)" ';
                placeholder = 'Keyword';
                remove = 'x'
            }

            var name = 'post_tags';
            if (target.data('name')) {
                name = target.data('name');
            }

            if (target.data('placeholder')) {
                placeholder = target.data('placeholder');
            }

            //TEMPLATES
            var tagTemplate = '<div class="tag"><input type="text" autocomplete="off" class="tag-input'
            + ' text-field" name="'+name+'[]" placeholder="'
            + placeholder +'"'+suggest+' value="" />'
            + '<a class="remove" href="javascript:void(0)">'
            + remove
            + '</a></div>';

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

                    if (target.data('suggest')) {
                        width = (width * 2) + 5;
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
                    var currentTagCount = 0;
                    var currentTagValue = $(this).val();
                    $('div.tag input', target).each(function() {
                        if (currentTagValue === $(this).val()) {
                            count++;
                        }

                        currentTagCount++
                    });

                    if(count > 1) {
                        $(this).parent().remove();
                    }

                    if (target.data('tag-count') && target.data('tag-count') == 1) {
                        tagCount($(this), currentTagCount);
                    }
                });
            };

            var tagCount = function(element, count) {
                var skills = 10;

                if (count > 10) {
                    $(element).parent().remove();
                }

                difference = skills - count;

                if (difference < 0) {
                    difference = 0;
                }

                $(element).closest('form').find('.skills-count span').html(difference);
            };

            //EVENTS
            target.click(function(e) {
                if (target.data('tag-count') && target.data('tag-count') == 1) {
                    tagCount($(this), $('div.tag input').length);
                }

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
         * WYSIWYG
         */
        $(window).on('wysiwyg-init', function(e, target) {
            $(target).wysihtml5({
                "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                "emphasis": true, //Italics, bold, etc. Default true
                "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                "html": false, //Button which allows you to edit the generated HTML. Default false
                "link": false, //Button to insert a link. Default true
                "image": false, //Button to insert an image. Default true,
                "color": false, //Button to change color of font
                "blockquote": true, //Blockquote
                toolbar: {
                    "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                    "emphasis": true, //Italics, bold, etc. Default true
                    "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                    "html": false, //Button which allows you to edit the generated HTML. Default false
                    "link": false, //Button to insert a link. Default true
                    "image": false, //Button to insert an image. Default true,
                    "color": false, //Button to change color of font
                    "blockquote": true, //Blockquote
                  }
            });

            // Added this for iframe font body color
            $(target).css({'color':'#474747'});
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
                completed = 0,
                //if the file is too large
                large = false;

            //hiddens will have base 64
            $('input[type="hidden"]', target).each(function() {
                if(large) {
                    return;
                }

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

                //cant be more than 5MB
                if(file.size > 5000000) {
                    large = true;
                    return;
                }

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
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.floor((evt.loaded / evt.total) * 100);
                                bar.css('width', percentComplete + '%').html(percentComplete + '%');
                            }
                        }, false);

                        return xhr;
                    },
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

                        toastr.error(message);
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

                        toastr.success(config.complete);

                        //all hidden fields that could have possibly
                        //been converted has been converted
                        //submit the form
                        target.submit();
                    }
                });
            });

            if(large) {
                e.preventDefault();
                toastr.error('File too large. Max Size is 5MB');
                return false;
            }

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
         * File Drag and Drop
         */
        $(window).on('file-upload-init', function (e, target) {
            var container = $(target);

            container.on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $(this).css('border', '1px solid #8DABC4');
            });

            container.on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            container.on('drop', function (e) {
                $(this).css('border', '1px dotted #8DABC4');
                e.preventDefault();

                if(e.originalEvent.dataTransfer.files.length > 1) {
                    toastr.error('Please upload 1 file only');
                    return;
                }
                // store the files to input
                $('.file-upload input[type="file"]').prop('files', e.originalEvent.dataTransfer.files);

                // count the number of files selected
                var numFiles = $('.file-upload input:file')[0].files.length;
                // check for multiple files
                var multiple = '';
                if (numFiles > 1) {
                    multiple = 's';
                }

                // append the value
                $('.file-upload h2').html(numFiles + ' file'+multiple+' selected');

            });

            //prevernt page from reacting on drop and drop
            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                container.css('border', '1px dotted #8DABC4');
            });

            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

        });

        $('.file-upload input[type="file"]').change(function(){
            // count the number of files selected
            var numFiles = $('.file-upload input:file')[0].files.length;
            // check for multiple files
            var multiple = '';
            if (numFiles > 1) {
                multiple = 's';
            }

            // append the value
            $('.file-upload .file-count').html(numFiles + ' file'+multiple+' selected');
        })

        $(window).on('file-upload-click', function (e, target) {
            if($('#sendResume .form-questions #form_id').val()) {
                // submit application form
                if(!validateApplicationForm()) {
                    return false;
                }
            }

            // file upload
            if($('#sendResume #resumeType').val() != 'link') {
                var numFiles = $('.file-upload input:file')[0].files.length

                if (numFiles < 1) {
                    toastr.error('Please upload a file');
                    return;
                }

                var files = $('.file-upload input[type="file"]').prop('files'),
                    resumeInput = $(target).closest('.send-resume').find('input[name="resume_position"]');

                if (resumeInput.length && resumeInput.val() == '') {
                    $(resumeInput).closest('.form-group').addClass('has-error');
                    $(resumeInput).closest('.form-group').find('.help-text').html('Position is required');
                    toastr.error('There are some errors in the form');
                    return;
                }

                //We need to send dropped files to Server
                handleFileUpload(files, 'file-upload');
            } else {
                fileUpload(new FormData(), null, 0);
            }
        });

        /**
         * Global toggle checkboxes on list
         */
        $(document).on('change', '.checkbox-all', function () {
            var state = $(this).is(':checked');
            $('input[type="checkbox"]').prop('checked', false);
            //check if checked then check all input checkboxes
            if (state) {
                $('input[type="checkbox"]').prop('checked', true);
            }
        });

        $(document).on('change', '.checkbox-single', function () {
            var state = $(this).is(':checked');

            if (!state) {
                $('.checkbox-all').prop('checked', false);
            }
        });

        /**
         * ATS Inform Seekers Button
         */
        $(window).on('ats-inform-seeker-click', function(e, target) {
            var postId = $(target).data('post-id');
            var formId = $(target).data('form-id');

            var data = {
                'post_id': postId,
                'form_id': formId,
            };

            //submit application form˚
            $.ajax({
                url: '/ajax/post/form/inform',
                type: 'POST',
                async: false,
                data: data,
                beforeSend: function () {
                    $(target).find('i.fa').removeClass('hide');
                },
                success: function (response) {
                    $(target).find('i.fa').addClass('hide');
                    if (response.error) {
                        toastr.error(response.message);
                    } else {
                        toastr.success(response.message);
                        setTimeout(function () {
                            return window.location.reload();
                        }, 1500);
                    }
                }
            });
        });

        $(window).on('ats-inform-single-seeker-click', function(e, target) {
            // Display toast message after clicking the notify button
            toastr.info('Notifying Applicant');


            var postId = $(target).data('post-id');
            var profileId = $(target).data('profile-id');
            var formId = $(target).data('form-id');

            var data = {
                'post_id' : postId,
                'profile_id' : profileId,
                'form_id' : formId,
            };

            $.post('/ajax/post/form/inform/seeker', data, function (response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    toastr.error(response.message);

                    // Enable the Notify button
                    $(target).html('Notify').removeAttr('disabled');

                    return false;
                }

                // At this point, there are no errors
                toastr.success(response.message);

                // Checks if the user is a new applicant
                if (response.applicant) {
                    // Changes the applicant elements
                    var appId = response.applicant.applicant_id;
                    var parentElement = $(target).parent().parent().parent().parent();
                    var elementId = 'applicant-detail-list-'+appId;
                    parentElement.attr('id', elementId);

                    elementId = 'post-'+appId;
                    parentElement.children().attr('id', elementId);

                    elementId = 'post-checkbox-'+appId;
                    parentElement.children().children().find('input')
                        .attr('id', elementId)
                        .attr('data-applicant-id', appId)
                        .attr('data-post-id', appId)
                        .val(appId);

                    parentElement.children().children().find('label')
                        .attr('for', elementId);

                    parentElement.children().children().find('a')
                        .attr('data-parent', '#related-accordion-'+appId)
                        .attr('href', '#post-accordion-'+appId);

                    elementId = 'applicant-detail-list-'+appId;
                    $('#'+elementId).find('.list-date').html(response.applicant.applicant_created);
                    $('#'+elementId).find('.list-form').html('Pending - No Answer');
                    setTimeout(function () {
                        return window.location.reload();
                    }, 1500);
                }
            });
        });

        $(window).on('attach-form-click', function(e, target) {
            var formId = $(target).data('form-id')
            var postId = $(target).data('post-id')
            var formName = $(target).data('form-name')

            var data = {
                'form_id': formId,
                'post_id': postId,
                'form_name': formName,
            }

            attachForm(data, target);
        });

        /**
         * Attach Form
         */
        var attachForm = function (data, target) {
            //submit application form
            $.ajax({
                url: '/ajax/post/attach/form',
                type: 'POST',
                async: false,
                data: { 'form_id': data.form_id, 'post_id': data.post_id },
                success: function (response) {
                    if (response.error) {
                        toastr.error(response.message);
                    } else {
                        toastr.success(response.message);
                        $(target).parent().parent().parent().html(data.form_name);
                        setTimeout(function () {
                            return window.location.reload();
                        }, 1500);
                    }
                }
            });

            return;
        };

        /**
         * Bulk Applicant Attach Label
         */
        $(window).on('bulk-attach-label-click', function(e, target) {
            var labelName = $(target).data('label');
            var ids = [];
            //get all checked checkboxes
            var applicationIds = $('input[name="applicant_ids[]"]:checked').each(function() {
                if ($(this).val()) {
                    ids.push($(this).val());
                }
            });

            var data = {
                'applicant_ids': ids,
                'label_name': labelName,
            };

            if (ids) {
                attachLabel(data, target);
            }

            $('.page-tracking-post-detail .content .detail .detail-list .panel-heading .list-label span i')
            .replaceWith("<span title=" + labelName + ">" + labelName + "</span>");


            setTimeout(function () {
                return window.location.reload();
            }, 1000);
        });

        /**
         * Bulk Applicant Attach Label
         */
        $(window).on('bulk-remove-applicant-click', function(e, target) {
            var ids = [];
            //get all checked checkboxes
            var applicationIds = $('input[name="applicant_ids[]"]:checked').each(function() {
                if ($(this).val()) {
                    ids.push($(this).val());
                }
            });

            var data = {
                'applicant_ids': ids,
                'label_name': 'Illegible'
            };

            if (ids) {
                attachLabel(data, target);
            }

            setTimeout(function () {
                 return window.location.reload();
            }, 1000);
        });

        /**
         * Applicant Attach Label
         */
        $(window).on('attach-label-click', function(e, target) {
            var applicantId = $(target).data('applicant-id');
            var labelName = $(target).data('label');

            var data = {
                'applicant_id': applicantId,
                'label_name': labelName,
            };

            attachLabel(data, target);
            $('.page-tracking-post-detail .content .detail .detail-list .panel-heading .list-label span i')
            .replaceWith("<span title=" + labelName + ">" + labelName + "</span>");

            setTimeout(function () {
                return window.location.reload();
            }, 1000);

        });

        var handleFileUpload = function (files, container) {

            for (var i = 0; i < files.length; i++) {
                var fd = new FormData();
                fd.append('file', files[i]);

                fileUpload(fd, files[i], i);
            }
        };

        $(window).on('file-uploading-change', function(e, target) {
            if(!$('#sendResume .form-questions #form_id').val()) {
                toastr.info('Uploading File...');

                var numFiles = $('.file-upload input:file')[0].files.length

                if (numFiles < 1) {
                    toastr.error('Please upload a file');
                    return;
                }

                var files = $('.file-upload input[type="file"]').prop('files');

                $('#resumeUpload input[name="type"]').val('create');

                //We need to send dropped files to Server
                handleFileUpload(files, 'file-upload');
            }
        });

        var fileUpload = function (formData, meta, count) {
            $('button[data-do="file-upload"]').attr('disabled', 'disabled');

            var queryString = $("#resumeUpload :input").filter(function () {
                    return !!this.value;
                }).serializeArray();;

            var postId;
            //add resume position form data
            $.each(queryString, function(index, value) {
                formData.append(value.name, value.value )
                if (value.name == 'post_id') {
                    postId = value.value;
                }
            });

            $.ajax({
                url: '/ajax/file/upload',
                type: 'POST',
                contentType: false,
                processData: false,
                cache: false,
                data: formData,
                success: function (data) {
                    var response = JSON.parse(data);

                    if(response.error) {
                        toastr.error(response.message);
                        $('button[data-do="file-upload"]').removeAttr('disabled');
                        $('.send-resume .form-group').addClass('has-error');
                        $('.send-resume .help-text').html(response.validation.resume_position);

                        return;
                    }

                    if (!response.error) {
                        /// set application form
                        if($('#sendResume .form-questions #form_id').val()) {
                            // submit application form
                            applicationSubmitForm();
                        }

                        toastr.success('Resume was uploaded');

                        $.get(window.location.href, function (data, status) {
                            $('.page-profile-resume-search .container .row.profile-panel').replaceWith($(data).find('.container .row.profile-panel'));
                        });

                        $('#sendResume').modal('hide');

                        if(postId != null) {
                            toastr.info('Notifying user');

                            setTimeout(function () {
                                $.get('/ajax/post/like/' + postId, function(response) {
                                    if(response.error) {
                                        toastr.error(response.message);
                                        return;
                                    }

                                    $('.interested[data-id="' + postId  + '"]').css('pointer-events', 'none');
                                    var badge = $('.interested[data-id="' + postId  + '"] span.like-count');
                                    var heart = $('.interested[data-id="' + postId  + '"] i.fa-heart-o');
                                    var count = parseInt(badge.text()) || 0;
                                    heart.removeClass('fa-heart-o').addClass('fa-heart');
                                    badge.removeClass('hide').text(++count);
                                    if (postType == 'seeker') {
                                        $('.toast').ready(function() {
                                            $('.interested-seeker').show().delay(2000).fadeTo(500, 1).fadeTo(2000, 0);
                                        });
                                    } else {
                                        toastr.success('User is being notified of your interest');
                                    }
                                });
                            }, 500);
                        }

                    }

                    //reset
                    $('button[data-do="file-upload"]').removeAttr('disabled');
                    $('.send-resume .form-group').removeClass('has-error');
                    $('.send-resume .help-text').html('');
                    $('.send-resume input[name="resume_position"]').val('');
                }
            });
        };

        $(document).on('click', '.choose-file', function (e, target) {
            $('.file-upload input[type="file"]').trigger('click');
        });

        var mimeExtensions = {
            'application/mathml+xml': 'mathml',
            'application/msword': 'doc',
            'application/oda': 'oda',
            'application/ogg': 'ogg',
            'application/pdf': 'pdf',
            'application/rtf': 'rtf',
            'application/rdf+xml': 'rdf',
            'application/vnd.mif': 'mif',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
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

        // form submit using ajax
        $(window).on('form-ajax-submit', function(e, target) {
            var $target = $(target),
                container = $target.data('container'),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // reset the class info
            $target.find('.form-group')
                .removeClass('has-error')
                .find('.help-block')
                .html('');

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    if (!response.validation) {
                        toastr.error(response.message);

                        return false;
                    }

                    toastr.error(response.message);

                    $.each(response.validation, function(key, message) {
                        var element = $('input[name="'+key+'"], select[name="'+key+'"]', $target);

                        element.parents('.form-group').addClass('has-error');
                        element.parents('.form-group').find('.help-text').html(message);
                    });

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    return false;
                }
                if (response.results) {
                    // process success
                    toastr.success(response.results);

                    setTimeout(function () {
                        return window.location.reload();
                    }, 2000);
                }
            }, 'json');
            return false;
        });

        // Sends a message to a user
        $(window).on('profile-message-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).attr('disabled', 'disabled');
            $(target).html('Sending <i class="fa fa-spinner fa-pulse"></i>')

            var data = $('#profile-message').formToJson();
            var url = '/ajax/profile/message';

            // Sends the data to be emailed
            $.post(url, data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    // Send an error message
                    toastr.error(response.message);

                    // Allow the button to be submitted again
                    $(target).html('Send');
                    $(target).removeAttr('disabled');
                    return false;
                }

                // Assume that there are no errors
                // Send a success message
                toastr.success(response.message);

                // Reloads the page after 2.5 seconds
                delayedRedirect('self', 2500);
                return false;
            });
        });
    })();

    /**
     * Billing Information
     */
    (function() {
        /**
         * City
         */
        $(window).on('city-init', function(e, target) {
            target = $(target);
            // if city already have a value set data
            if (target.data('value')) {
                // set data
                var data = {
                    filter: {
                        // use the city name to get the parent_id
                        area_name: target.data('value')
                    },
                    order: {
                        'area_name': 'ASC'
                    },
                    range: 0
                };
                function ajaxCall() {
                    var parentId;
                    $.get('/ajax/area/search', data, function(response) {
                        // any error?
                        if (response.error) {
                            setTimeout(ajaxCall, 3000);
                            return;
                        }
                        // expected to have one result which is the parent of the city
                        $.each(response.results, function(key, val) {
                            parentId = val.area_parent;
                        });
                        // set data again and use the parent_id as filter
                        var data = {
                            filter: {
                                area_parent: parentId
                            },
                            order: {
                                'area_name': 'ASC'
                            },
                            range: 0
                        };
                        function ajaxCall() {
                            $.get('/ajax/area/search', data, function(response) {
                                // any error?
                                if (response.error) {
                                    setTimeout(ajaxCall, 3000);
                                    return;
                                }
                                // add option tag
                                $(target).append($('<option>')
                                    .attr('value', target.data('value'))
                                    .html(target.data('value')));
                                // expected to have the list of cities with the same parent
                                // add state per option
                                $.each(response.results, function(key, val) {
                                    $(target).append($('<option>')
                                        .attr('value', val.area_name)
                                        .html(val.area_name));
                                });
                                $(target).chosen({
                                    width: '100%',
                                    selected: true,
                                    placeholder_text_single: 'Select City...',
                                });
                            }, 'json');
                        }
                        ajaxCall();
                    }, 'json');
                }
                ajaxCall();
            } else {
                // add empty option tag
                $('#city').append($('<option>')
                    .attr('value', '')
                    .html('Select City...'));
                $('#city').chosen({
                    width: '100%',
                    placeholder_text_single: 'Select City...'
                });
            }
        });

        /**
         * State/Province
         */
        $(window).on('state-init', function(e, target) {
            target = $(target);
            //set data
            var data = {
                filter: {
                    area_type: 'state'
                },
                order: {
                    'area_name': 'ASC'
                },
                range: 0
            };

            function ajaxCall() {
                $.get('/ajax/area/search', data, function(response) {
                    if (response.error) {
                        setTimeout(ajaxCall, 3000);
                        return;
                    }
                    if (target.data('value')) {
                        $(target).append($('<option>')
                            .attr('value', target.data('value'))
                            .html(target.data('value')));
                        // add state per option
                        $.each(response.results, function(key, val) {
                            $(target).append($('<option>')
                                .attr('value', val.area_id)
                                .html(val.area_name));
                        });
                        $(target).chosen({
                            width: '100%',
                            selected: true,
                            placeholder_text_single: 'Select State...',
                        });
                    } else {
                        // add empty option tag
                        $(target).append($('<option>')
                            .attr('value', '')
                            .html('Select State...'));
                        // add state per option
                        $.each(response.results, function(key, val) {
                            $(target).append($('<option>')
                                .attr('value', val.area_id)
                                .html(val.area_name));
                        });
                        $(target).chosen({
                            width: '100%',
                            placeholder_text_single: 'Select State...'
                        });
                    }
                }, 'json');
            }
            ajaxCall();
        // get city based on state selected
        }).on('state-change', function(e, target) {
            value = $(target).val();
            element = $('#city');
            // because we initialize an empty <select id="city"> .chosen()
            // we need to destroy it first so we have a fresh copy.
            // source: https://harvesthq.github.io/chosen/
            element.chosen('destroy');
            //set data
            var data = {
                filter: {
                    area_parent: value,
                    area_type: 'city'
                },
                order: {
                    'area_name': 'ASC'
                },
                range: 0
            };
            $('input[name=profile_address_state]').val($(target).find(":selected").text());
            function ajaxCall() {
                $.get('/ajax/area/search', data, function(response) {
                    if (response.error) {
                        setTimeout(ajaxCall, 3000);
                        return;
                    }
                    element.children('option:not(:first)').remove();
                    // each state per option
                    $.each(response.results, function(key, val) {
                        element.append($('<option>')
                            .attr('value', val.area_name)
                            .html(val.area_name));
                    });
                    // initialize chosen
                    element.chosen({
                        width: '100%',

                    });
                }, 'json');
            }
            ajaxCall();
        });

        /**
         * Credit History- Change Service Type
         */
        $(window).on('profile-credit-filter-service-type-click', function(e, target) {
            $('input[name="filter[service_name]"]').val($(target).data('value'));
            $('.service-name').html($(target).html());
            $('.toggle-name').parent().removeClass('open');
            $('.toggle-name').attr('aria-expanded', 'false');
            return false;
        });

        /**
         * Credit History- Change Service Date
         */
        $(window).on('profile-credit-filter-service-date-click', function(e, target) {
            $('.service-range').html($(target).html());
            var trimmedContent = $.trim($(target).html());
            $('input[name="service_range"]').val(trimmedContent);

            $('.toggle-range').parent().removeClass('open');
            $('.toggle-range').attr('aria-expanded', 'false');

            // Checks if there is no value
            if ($(target).data('value') == 'custom') {
                // Empty the input field
                $('input[name="filter[date][start_date]"]').val('');

                // Show the date ranges
                $('.view-all .dates').css('display', 'inline-block');
                return false;
            }

            // Assume that this is not a custom date range
            $('.view-all .dates').css('display', 'none');

            $('input[name="filter[date][start_date]"]').val('');
            $('input[name="filter[date][start_date]"]').val($(target).data('value'));

            return false;
        });

        $(window).on('profile-credits-form-init', function(e, target) {
            if ($('input[name="service_range"]').val() == 'Custom') {
                // Show the date ranges
                $('.view-all .dates').css('display', 'inline-block');
            }

            return false;
        });
    })();

    /**
     * Madlibs UI
     */
    (function() {
        $(window).on('madlibs-seeker-click', function(e, target) {
            $('form.madlibs-seeker-form').removeClass('hide');
            $('form.madlibs-poster-form').addClass('hide');

            $('.partial-match-me.match-seeker').removeClass('hide');
            $('.partial-match-me.match-poster').addClass('hide');

            $('a.madlibs-seeker-trigger').removeClass('btn-poster');
            $('a.madlibs-seeker-trigger').addClass('btn-seeker');

            $('a.madlibs-poster-trigger').removeClass('btn-seeker');
            $('a.madlibs-poster-trigger').addClass('btn-poster');

            $('.home-jobs-post .popular-seekers').addClass('hide');
            $('.home-jobs-post .featured-jobs').removeClass('hide');
        });

        $(window).on('madlibs-poster-click', function(e, target) {
            $('form.madlibs-poster-form').removeClass('hide');
            $('form.madlibs-seeker-form').addClass('hide');

            $('.partial-match-me.match-poster').removeClass('hide');
            $('.partial-match-me.match-seeker').addClass('hide');

            $('a.madlibs-poster-trigger').removeClass('btn-poster');
            $('a.madlibs-poster-trigger').addClass('btn-seeker');

            $('a.madlibs-seeker-trigger').removeClass('btn-seeker');
            $('a.madlibs-seeker-trigger').addClass('btn-poster');

            $('.home-jobs-post .popular-seekers').removeClass('hide');
            $('.home-jobs-post .featured-jobs').addClass('hide');
        });

        $(window).on('madlibs-form-submit', function(e, target) {
            var errors = false;
            var fields = [
                'post_name',
                'post_location',
                'post_position'
            ];

            fields.forEach(function(field) {
                var node = $('input[name="' + field + '"]', target);
                if(!node.val().length) {
                    node.addClass('has-error');
                    errors = true;
                }
            });

            if(errors) {
                e.preventDefault();
                toastr.error('All fields are required');
                return false;
            }

        });
    })();

    /**
     * Madlibs UI
     */
    (function() {
        $(window).on('research-form-init', function(e, target) {

            $(target).find('.job-salaries').click(function() {
                $(this).addClass('active');
                $('.job-location').removeClass('active');
                $('.research-position').show();
                $('.research-location').css('width', '50%');
            });

            $(target).find('.job-location').click(function() {
                $(this).addClass('active');
                $('.job-salaries').removeClass('active');
                $('.research-position').hide();
                $('.research-location').css('width', '100%');
            });
        });
    })();


    /**
     * Wall UI
     */
    (function() {
        $(window).on('wall-more-click', function(e, target) {
            $(target).prev().remove();
            $(target).next().removeClass('hide');

            $('div.post-detail', $(target).parent().parent()).removeClass('hide');
            $(target).remove();
        });

        $(window).on('verify-click', function(e, target) {
            e.preventDefault();

            target = $(target);

            var email = $(target).attr('data-email');
            $.get('/ajax/account/verify/' + email, function(response) {
                if(response.error) {
                    toastr.error(response.message);
                    return;
                }

                $(target).parents('.form-group').remove();

                toastr.success('Please check your email to activate your account');
            });
        });

        $(window).on('post-like-click', function(e, target) {
            target = $(target);
            var id = target.attr('data-id');
            postType = $.trim(target.attr('data-type'));

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            if (target.attr('data-enabled') != 1) {
                $('#post-'+id+' .not-logged-in')
                    .removeClass('hide');

                // Checks if the parent if the similar posts container
                if ($('#post-'+id).parent().parent('.similar-posts').length) {
                    var checkHeight = $('#post-'+id).css('height').replace('px', '');
                    checkHeight = parseInt(checkHeight);

                    // Checks if the height is not too big
                    if (checkHeight < 500) {
                        checkHeight = parseInt(checkHeight) + 485;
                        $('#post-'+id).css('height', checkHeight);
                    }
                }

                toastr.error('You are not logged in');
                var href = target.attr('href');

                //redirect to login page
                window.location = href;

                return false;
            }

            // poster continue post like
            if (target.attr('data-type') == 'poster') {
                postLike(id, postType);
                return;
            }

            //set post id
            $('#question-modal #no-thanks').attr('data-id', id);
            $('#question-modal #post-apply').attr('data-id', id);

            //reset questions
            $('#question-modal .form-questions .form-question').not('.hide').html('');
            $('#question-modal .form-questions #form_id').val('');

            $('#question-modal').find('#post-apply').attr('data-do', 'post-apply').doon();

            // get the form and questions
            $.get('/ajax/post/form/' + id, function(response) {
                // Checks if there are no errors
                if (!response.error) {
                    var form = response.results.form;
                    var questions = response.results.questions;

                    if (form && questions) {
                        // Populate the form via JS
                        populateForm(form, questions);

                        // show the question modal
                        $('#question-modal').modal('show');
                    }
                } else {
                    // notify info
                    toastr.info('Notifying user');

                    // post like
                    postLike(id, postType);
                }

                // enable the button
                $(target).css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                return false;
            });
        });

        $(window).on('post-like-no-thanks-click', function(e, target) {
            target = $(target);
            var id = target.attr('data-id');

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // notify info
            toastr.info('Notifying user');

            // post like
            postLike(id);

            // enable the button
            $(target).css({
                "cursor": "pointer",
                "pointer-events": "auto"
            });
        });

        $(window).on('post-apply-click', function (e, target) {
            var id = $(target).data('id');
                target = $(target);
                postType = $.trim(target.attr('data-type'));

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            if ($('#question-modal .form-questions #form_id').val()) {
                // validate application form
                if(!validateApplicationForm()) {

                    // enable the button
                    $(target).css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    return false;
                }
            }

            // submit application form
            if (applicationSubmitForm()) {
                postLike(id, postType);
            }
        });

        /**
         *
         * Interested Claim
         *
         */
        $(window).on('interested-claim-submit', function (e, target) {
            var $target = $(target),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // reset the class info
            $target.find('.form-group')
                .removeClass('has-error')
                .find('.help-text')
                .html('');

            // disable the button
            $(target)
                .find('button[type="submit"]')
                .css({
                    "cursor": "wait",
                    "pointer-events": "none"
                });

            // add button spinner
            $(target)
                .find('button[type="submit"]')
                .append('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');

            var checkProfile = false;
            $.ajax({
                url: '/ajax/interested/claim/check',
                type: 'POST',
                async: false,
                data: data,
                success: function (response) {
                    // if there are errors
                    if (response.error) {
                        if (!response.validation) {
                            toastr.error(response.message);

                            // enable the button
                            $(target)
                                .find('button[type="submit"]')
                                .css({
                                    "cursor": "pointer",
                                    "pointer-events": "auto"
                                });

                            // remove button spinner
                            $(target)
                                .find('.fa-spinner')
                                .remove();

                            checkProfile = true;
                        }

                        toastr.error(response.message);

                        $.each(response.validation, function (key, message) {
                            var element = $('input[name="' + key + '"], select[name="' + key + '"]', $target);

                            element.parents('.form-group').addClass('has-error');
                            element.parents('.form-group').find('.help-text').html(message);
                        });

                        // enable the button
                        $(target)
                            .find('button[type="submit"]')
                            .css({
                                "cursor": "pointer",
                                "pointer-events": "auto"
                            });

                        // remove button spinner
                        $(target)
                            .find('.fa-spinner')
                            .remove();

                        checkProfile = true;
                    }
                }
            });

            if (checkProfile) {
                return false;
            }

            var alreadyInterested = false,
                alreadyInterestedMsg = null;
            $.ajax({
                url: '/ajax/post/like/detail/' + data.post_id + '/' + data.profile_email,
                type: 'GET',
                async: false,
                success: function (response) {
                    if (response.results) {
                        alreadyInterested = true
                        alreadyInterestedMsg = response.results;
                    }
                }
            });

            if (alreadyInterested) {
                toastr.error(alreadyInterestedMsg);

                // enable the button
                $(target)
                    .find('button[type="submit"]')
                    .css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                // remove button spinner
                $(target)
                    .find('.fa-spinner')
                    .remove();

                return false;
            }

            //reset questions
            $('#question-modal .form-questions .form-question').not('.hide').html('');
            $('#question-modal .form-questions #form_id').val('');

            $('#question-modal').find('#post-apply').attr('data-do', 'post-claim').doon();

            var formQuestions = false;
            //submit application form
            $.ajax({
                url: '/ajax/post/form/' + data.post_id,
                type: 'GET',
                async: false,
                success: function (response) {
                    if (!response.error) {
                        var form = response.results.form;
                        var questions = response.results.questions;

                        if (form && questions) {
                            // Populate the form via JS
                            populateForm(form, questions);

                            $('#question-modal').find('#post-apply').attr('data-post-id', data.post_id);
                            $('#question-modal').find('#post-apply').attr('data-profile-name', data.profile_name);
                            $('#question-modal').find('#post-apply').attr('data-profile-email', data.profile_email);
                            $('#question-modal').find('#post-apply').attr('data-profile-phone', data.profile_phone);

                            // show the question modal
                            $('#question-modal').modal('show');

                            // enable the button
                            $(target)
                                .find('button[type="submit"]')
                                .css({
                                    "cursor": "pointer",
                                    "pointer-events": "auto"
                                });

                            // remove button spinner
                            $(target)
                                .find('.fa-spinner')
                                .remove();

                            formQuestions = true
                        }
                    }
                }
            });

            // if there's a question form do not proceed
            if (formQuestions) {
                return false;
            }

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    if (!response.validation) {
                        toastr.error(response.message);

                        // enable the button
                        $(target).find('button[type="submit"]').css({
                            "cursor": "pointer",
                            "pointer-events": "auto"
                        });

                        // remove button spinner
                        $(target)
                            .find('.fa-spinner')
                            .remove();

                        return false;
                    }

                    toastr.error(response.message);

                    $.each(response.validation, function (key, message) {
                        var element = $('input[name="' + key + '"], select[name="' + key + '"]', $target);

                        element.parents('.form-group').addClass('has-error');
                        element.parents('.form-group').find('.help-text').html(message);
                    });

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    // remove button spinner
                    $(target)
                        .find('.fa-spinner')
                        .remove();

                    return false;
                }

                // enable the button
                $(target)
                    .find('button[type="submit"]')
                    .css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                // remove button spinner
                $(target)
                    .find('.fa-spinner')
                    .remove();

                // success message
                toastr.success('User is being notified of your interest');
                toastr.success(response.results);
            }, 'json');

            return false;
        });

        $(window).on('post-claim-click', function (e, target) {
            var target = $(target),
                postId = target.attr('data-post-id'),
                profileName = target.attr('data-profile-name'),
                profileEmail = target.attr('data-profile-email'),
                profilePhone = target.attr('data-profile-phone');

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            if ($('#question-modal .form-questions #form_id').val()) {
                // validate application form
                if (!validateApplicationForm()) {

                    // enable the button
                    $(target).css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    return false;
                }
            }

            // set the data
            var data = {
                'post_id': postId,
                'profile_email' : profileEmail,
                'profile_name' : profileName,
                'profile_phone' : profilePhone
            };

            var alreadyInterested = false;
            $.ajax({
                url: '/ajax/interested/profile',
                type: 'POST',
                async: false,
                data: data,
                success: function (response) {
                    if (response.error) {
                        toastr.error(response.message);

                        // enable the button
                        $(target).css({
                            "cursor": "pointer",
                            "pointer-events": "auto"
                        });

                        alreadyInterested = true;
                    }

                    // append the profile id
                    $('#question-modal').find('#profile_id').val(response.results);
                }
            });

            if (alreadyInterested) {
                return;
            }

            // submit application form
            if (applicationSubmitForm()) {
                // enable the button
                $(target).css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                // show the question modal
                $('#question-modal').modal('hide');

                // success message
                toastr.success('User is being notified of your interest');
            }
        });

        var postLike = function (id, postType) {
            $.get('/ajax/post/like/' + id, function(response) {
                if(response.error) {
                    toastr.error(response.message);
                    return;
                }

                $('.interested[data-id="' + id  + '"]').css('pointer-events', 'none');
                var badge = $('.interested[data-id="' + id  + '"] span.like-count');
                var heart = $('.interested[data-id="' + id  + '"] i.fa-heart-o');
                var count = parseInt(badge.text()) || 0;
                badge.removeClass('hide').text(++count);
                heart.removeClass('fa-heart-o').addClass('fa-heart');
                if (postType == 'seeker') {
                    $('.toast').ready(function() {
                        $('.interested-seeker').show().delay(2000).fadeTo(500, 1).fadeTo(2000, 0);
                    });
                } else {
                    toastr.success('User is being notified of your interest');
                }
                if (response.results && response.results.credits) {
                    toastr.info('You earned '
                        + response.results.credits
                        + ' experience points');
                }
                if (response.results && response.results.badge) {
                    $('.achievement-modal .badge-image img')
                        .attr('src', response.results.badge.image)
                        .attr('alt', response.results.badge.message);
                    $('.achievement-modal .message')
                        .text(response.results.badge.message);
                    $('.achievement-modal').modal('show');
                }

                // show the question modal
               $('#question-modal').modal('hide');

               setTimeout(function(){
                   // show the completeness modal
                   $('#completeness-modal').modal('show');
               }, 1000);
            });
        };

        // Reusable function to populate and show ATS Form with Questions
        var populateForm = function(form, questions) {
            $('#question-modal .form-questions').removeClass('hide');

            // add form_id
            $('#question-modal .form-questions #form_id').val(form.form_id);

            // add post_id
            $('#question-modal .form-questions #post_id').val(form.post_id);

            // Loops through the questions
            $.each(questions, function(index, question) {
                // Clones the question form
                var questionElement = $('#question-modal .form-questions').find('.form-question.hide').clone();

                // Show the element
                questionElement.removeClass('hide');

                // Append the question_name
                questionElement.find('.question-name').html(question.question_name);

                // Loops through the question_choices
                $.each(question.question_choices, function(i, choice) {

                    // Clone the answer element
                    var questionAnswer = questionElement.find('.question-answer.hide').clone();

                    // Show the element
                    questionAnswer.removeClass('hide');

                    // Varaible declaration for replacing
                    var answerName = 'question[' + question.question_id + ']';
                    questionAnswer.find('input').attr('name', answerName).val(choice);
                    questionAnswer.find('input').attr('id', question.question_id+'-question-'+i);
                    questionAnswer.find('label').html(choice);
                    questionAnswer.find('label').attr('for', question.question_id+'-question-'+i);
                    questionElement.find('.question-answers').append(questionAnswer);
                });

                // Checks if custom answers are allowed
                if ($.inArray('custom', question.question_type) !== -1) {
                    // Varaible declaration for replacing
                    var answerName = 'question[' + question.question_id + ']';

                    // Show the input field / custom_answer
                    custom = questionElement.find('.question-answers .custom-answer')
                        .removeClass('hide')
                        .attr('name', answerName);

                    if (questionElement.find('.question-answer .choice-answer').length == 1) {
                        custom.addClass('custom-answer-only');
                    } else {
                        questionElement.find('.question-answers').append(custom);

                        // Clone the answer element
                        var questionAnswer = questionElement.find('.question-answer.hide').clone();

                        // Show the element
                        questionAnswer.removeClass('hide');
                        questionAnswer.find('input').addClass('hide');

                        // Varaible declaration for replacing
                        var answerName = 'question[' + question.question_id + ']';
                        questionAnswer.find('input').attr('name', answerName);
                        questionAnswer.find('input').attr('id', 'customAnswer');
                        questionAnswer.find('label').addClass('hide');
                        questionElement.find('.question-answers').append(questionAnswer);

                    }
                }

                // Checks if custom answers are allowed
                if ($.inArray('file', question.question_type) !== -1) {
                    // Varaible declaration for replacing
                    var answerName = 'question[' + question.question_id + ']';

                    // Show the input field / custom_answer
                    questionElement.find('.question-answers .request-file')
                        .removeClass('hide');

                    questionElement.find('.question-answers .request-file')
                        .next('input')
                        .attr('name', answerName);
                }

                // Apend the entire question
                $('#question-modal .form-questions').append(questionElement).doon();
            });
        };

        var applicationSubmitForm = function () {

            var queryString = $("#question-modal :input").filter(function () {
                    return !!this.value;
                }).serializeArray();;
            var succeed = false;

            //submit application form
            $.ajax({
                url: '/ajax/applicant/submit/form',
                type: 'POST',
                async: false,
                data: queryString,
                success: function (response) {
                    if (response.error) {
                        toastr.error(response.message);
                        succeed = false;

                        if (response.message == 'Application was already submitted') {
                            succeed = true;
                        }
                    } else {
                        if (response.message) {
                            toastr.success(response.message);
                        }

                        succeed = true;
                    }
                }
            });

            return succeed;
        };

        $(window).on('add-new-resume-click', function(e, target) {

            //toggle design
            $('#sendResume .file-upload').toggleClass('hide');
            $('#sendResume .resume-position').toggleClass('hide');
            $('#sendResume hr').toggleClass('hide');
            $('#sendResume #resumeType').val(function(i, val) { return val == 'link' ? 'create' : 'link'});

            e.preventDefault();
        });

        $(window).on('post-download-click', function(e, target) {
            popupWindow = window.open("about:blank","directories=no,height=100,width=100");
            if(popupWindow) {
                popupWindow.close();
            }
            if (!popupWindow) {
                $('#popup-blocker').modal('show');
                return;
            }

            target = $(target);
            var id = target.attr('data-id');
            toastr.info('Processing your request... If it doesn\'t start downloading, check your popup blocker');
            var type = target.attr('data-type');

            if(type == 'resume') {
                var url = '/ajax/resume/download/' + id;
            } else {
                var url = '/ajax/post/download/' + id;
            }

            $.get(url, function(response) {
                if(response.error) {
                    if(response.validation && response.validation.code) {
                        switch(response.validation.code) {
                            case 'insufficient-credits':
                                var redirect = encodeURIComponent(window.location.href);
                                window.location = '/profile/credit/checkout?redirect_uri' + redirect;
                                return;
                            case 'already-downloaded':
                                if(typeof response.results === 'undefined'
                                    || typeof response.results.resume_link === 'undefined'
                                ) {
                                    return;
                                }

                                window.open(response.results.resume_link, '_blank');
                                return;
                        }
                    }

                    toastr.error(response.message);
                    return;
                }

                if (response.results.credits) {
                    toastr.info('You earned '
                        + response.results.credits
                        + ' experience points');
                }

                if (response.results.badge) {
                    $('.achievement-modal .badge-image img')
                        .attr('src', response.results.badge.image)
                        .attr('alt', response.results.badge.message);
                    $('.achievement-modal .message')
                        .text(response.results.badge.message);
                    $('.achievement-modal').modal('show');
                }

                var badge = target.next('span.badge');
                var count = parseInt(badge.text()) || 0;
                badge.removeClass('hide').text(++count);

                if (typeof response.results === 'undefined'
                    || typeof response.results.resume_link === 'undefined'
                ) {
                    return;
                }

                window.open(response.results.resume_link, '_blank');
            });
        });

        $(window).on('resume-download-init', function(e, target) {
            target = $(target);

            var url_string = window.location.href
            var url = new URL(url_string);
            var resumeId = url.searchParams.get("resume_id");

            var loggedIn = target.attr('data-logged-in');

            //if resume id
            if(resumeId != null) {
                 if(loggedIn == 'false') {
                    toastr.error('You are not logged in');
                    return;
                }

                //download resume
                $('a[data-id="' +  resumeId + '"]').click();
            }


        });

        $(window).on('information-resume-click', function(e, target) {
            var profile_id = $(target).data('profile-id'),
                information_id = $(target).data('information-id'),
                modal = $(target).data('modal'),
                url =  '/ajax/information/resume/download/'+profile_id+'/'+information_id;

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            $.get(url, function(response) {
                // check for response error
                if(response.error) {
                    toastr.error(response.message);

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    if (response.message == 'You just need 10 more credits to download this resume') {
                        var redirect = encodeURIComponent(window.location.href);
                         // Redirect to the add credit page
                        delayedRedirect('/profile/credit/checkout?redirect_uri' + redirect, 2500);
                    }

                    return;
                }

                // enable the button
                $(target).find('button[type="submit"]').css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                // open the resume download link
                window.open(response.results.url, '_self');

                // check if there's modal
                if (modal) {
                    $(modal).modal('hide');
                }

                if (response.results.credits) {
                    toastr.info('You earned '
                        + response.results.credits
                        + ' experience points');
                }

                if (response.results.badge) {
                    $('.achievement-modal .badge-image img')
                        .attr('src', response.results.badge.image)
                        .attr('alt', response.results.badge.message);
                    $('.achievement-modal .message')
                        .text(response.results.badge.message);
                    $('.achievement-modal').modal('show');

                    var badge = $(target).next('span.badge');
                    var count = parseInt(badge.text()) || 0;
                    badge.removeClass('hide').text(++count);
                }

                return;
            });
        });

        $(window).on('page-body-init', function(e, target) {
            $(window).scroll(function() {
                // get vertical position
                var top = $(this).scrollTop();

                // scroll top
                if (top > 65) {
                    // set top search bar content
                    if ($(target).find('.fixed-search-bar').is(':empty')) {
                        $(target).find('.post-search-form').clone().prependTo('.fixed-search-bar');
                    }

                    // show search bar
                    $(target).find('.fixed-search-bar').removeClass('hide');
                    return;
                }

                // hide search bar
                $(target).find('.fixed-search-bar').addClass('hide');
            });
        });

        /**
         * Post Search AJAX Pagination
         */
        $(window).on('post-search-ajax-init', function(e, target) {
            //figure out what page to go to (start)
            var start = 0,
                startSponsored = 0;
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

            $(window).scroll(function() {
                //if we are already paginating
                if(paginating) {
                    return;
                }

                var variableHeight = $(this).scrollTop() + $(window).height();
                var totalHeight = $(document.body).height();
                var percent = variableHeight / totalHeight;
                var range = 50;
                var rangeSponsored = 5;

                if(percent < .75) {
                    return;
                }

                paginating = true;
                start += range;
                startSponsored += rangeSponsored;
                var search = window.location.search;

                if (search.indexOf('start=') !== -1) {
                    search = search.replace(/start\=[0-9]+/ig, 'start=' + start);
                } else if (search.indexOf('?') !== -1) {
                    search += '&start=' + start;
                } else {
                    search = '?start=' + start;
                }

                // check for post type if poster
                if (window.location.href.indexOf('/Job-Search-Companies') !== -1) {
                    search += '&type=poster';
                }

                // check for post type if seeker
                if (window.location.href.indexOf('Job-Seekers-Search') !== -1) {
                    search += '&type=seeker';
                }

                // check for profile
                if (window.location.href.indexOf('/Companies/') !== -1 ||
                    window.location.href.indexOf('/Job-Seekers/') !== -1) {
                    var profileId = window.location.href.split('/').pop();
                        profileId = profileId.split('-').pop().substr(1);
                    search += '&profile=' + profileId;
                }

                // add start sponsored post
                search += '&start_sponsored=' + startSponsored;

                // show ajax loader
                loader.removeClass('hide');

                $.get('/ajax/post/search' + search, function(response) {
                    response = $(response);
                    if(!$('article', response).length) {
                        loader.addClass('hide');
                        return;
                    }

                    // append the results
                    $(target).append(response);

                    // hide ajax loader
                    loader.addClass('hide');

                    $(document.body).doon();

                    $('ul.loader-pagination li.active').removeClass('active').next().addClass('active');

                    paginating = false;
                });
            });
        });

        /**
        *
        * Promote Post
        */
        $(window).on('promote-post-click', function(e, target) {
            // set the data
            var data = {
                'post_id': $(target).data('id'),
                'post_promote' : 'promote',
                'reload' : $(target).data('reload')
            };

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // send post request
            $.post('/ajax/promote/post', data, function (response) {
                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);
                    // redirect
                    window.location.href = "/profile/credit/checkout";

                    return false;
                }

                // check if there's a result
                if (response.results) {
                    // show message
                    toastr.success(response.results.message);
                    if (response.results.credits) {
                        toastr.info('You earned '
                            + response.results.credits
                            + ' experience points');
                    }

                    setTimeout(function() {
                        // remove the element
                        $('#post-'+$(target).data('id')+' .tips .promote-post').remove();

                        // check if reload is possible
                        if (data.reload === 1) {
                            window.location.reload();
                        }
                    }, 2000);
                }

                return false;
            });
        });

        /**
         * SMS Notification
         */
        $(window).on('sms-notification-click', function(e, target) {
            // set the data
            var data = {
                'post_id': $(target).data('id'),
                'post_notify' : 'sms-match',
                'reload' : $(target).data('reload')
            };

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // send post request
            $.post('/ajax/promote/post', data, function (response) {
                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);
                    // redirect
                    window.location.href = "/profile/credit/checkout";

                    return false;
                }
                // check if there's a result
                if (response.results) {
                    // show message
                    toastr.success(response.results.message);
                    // remove the element
                    setTimeout(function() {
                        $('#post-'+$(target).data('id')+' .tips .sms-match').remove();

                        // check if reload is possible
                        if (data.reload === 1) {
                            window.location.reload();
                        }
                    }, 2000);
                }

                return false;
            });
        });

        /**
         * SMS Interest Notification
         */
        $(window).on('sms-interest-click', function(e, target) {
            // set the data
            var data = {
                'post_id': $(target).data('id'),
                'post_notify' : 'sms-interest',
                'reload' : $(target).data('reload')
            };

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // send post request
            $.post('/ajax/promote/post', data, function (response) {
                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);
                    // redirect
                    window.location.href = "/profile/credit/checkout";

                    return false;
                }
                // check if there's a result
                if (response.results) {
                    // show message
                    toastr.success(response.results.message);
                    // remove the element
                    setTimeout(function() {
                        $('#post-'+$(target).data('id')+' .tips .sms-interest').remove();

                        // check if reload is possible
                        if (data.reload === 1) {
                            window.location.reload();
                        }
                    }, 2000);
                }

                return false;
            });
        });

        /**
         * tips modal submit
         */
        $(window).on('tips-modal-submit', function(e, target) {
            var $target = $(target),
                container = $target.data('container'),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // check if post_location is empty
            if (container == '#location' && $.isEmptyObject(data)) {
                data.post_location = null;
            }

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // reset the class info
            $target.find('.form-group')
                .removeClass('has-error')
                .find('.help-block')
                .html('');

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    if (!response.validation) {
                        toastr.error(response.message);

                        // enable the button
                        $(target).find('button[type="submit"]').css({
                            "cursor": "pointer",
                            "pointer-events": "auto"
                        });

                        return false;
                    }

                    toastr.error(response.message);

                    $.each(response.validation, function(key, message) {
                        var element = $('input[name="'+key+'"], select[name="'+key+'"]', $target);

                        element.parents('.form-group').addClass('has-error');
                        element.parents('.form-group').find('.help-text').html(message);
                    });

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    return false;
                }

                if (response.results) {
                    // show message
                    toastr.success('Post was updated');
                    // process success
                    setTimeout(function () {
                        // check for experience
                        if (response.results.post_experience) {
                            // hide the modal
                            $(container).modal('hide');
                            // remove the element
                            $('#post-'+$(target).data('id')+' .tips .tips-body .experience').remove();
                        }
                        // check for location
                        if (response.results.post_location) {
                            // hide the modal
                            $(container).modal('hide');
                            // remove the element
                            $('#post-'+$(target).data('id')+' .tips .tips-body .location').remove();

                            // reset the chosen
                            $('.chosen-select').val('').trigger('chosen:updated');
                        }
                        // check for arrangement
                        if (response.results.post_arrangement) {
                            // hide the modal
                            $(container).modal('hide');
                            // remove the element
                            $('#post-'+$(target).data('id')+' .tips .tips-body .arrangement').remove();
                        }
                        // check for industry
                        if (response.results.post_industry) {
                            // hide the modal
                            $(container).modal('hide');
                            // remove the element
                            $('#post-'+$(target).data('id')+' .tips .tips-body .industry').remove();
                        }

                        if(!$('#post-'+$(target).data('id')+' .post-tips .tips-body > div').length) {
                            $('#post-'+$(target).data('id')+' .post-tips').remove();
                        }
                        window.location.reload();
                    }, 2000);
                }
                setTimeout(function () {
                  $("button.close").click();
                }, 2000);
            }, 'json');


            return false;
        });

        /**
         * poster modal
         */
        $(window).on('poster-modal-click', function(e, target) {
            var $target = $(target),
                id = $target.data('id'),
                container = $target.data('container');

            // clear input
            $('#'+container).find('input[type="text"]').val('');
            // append action
            $('#'+container).find('form').attr('action', '/ajax/post/update/'+id);
            if (container == "school") {
                $('#'+container).find('form').attr('action', '/ajax/school/update/'+id);
            }
            // append id
            $('#'+container).find('form').attr('data-id', id);
            // reset the chosen
            $('.chosen-select').val('').trigger('chosen:updated');
            // enable the button
            $('#'+container).find('button[type="submit"]').css({
                "cursor": "pointer",
                "pointer-events": "auto"
            });
        });

        /**
         * arrangement
         */
        $(window).on('arrangement-full-click', function(e, target) {
            var $target = $(target),
                id = $target.data('id'),
                container = $target.data('container');

            // set the data
            var data = {
                'post_id': id,
                'post_arrangement': 'full-time'
            };

            // send post request
            $.post('/ajax/post/update/'+id, data, function (response) {
                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);
                    // redirect
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);

                    return false;
                }

                // check if there's a result
                if (response.results) {
                    // show message
                    toastr.success('Post Was Updated');
                    // remove the element
                    setTimeout(function() {
                        $('#post-'+$(target).data('id')+' .tips .arrangement').remove();

                        if(!$('#post-'+$(target).data('id')+' .post-tips .tips-body > div').length) {
                            $('#post-'+$(target).data('id')+' .post-tips').remove();
                        }
                        window.location.reload();
                    }, 2000);
                }

                return false;
            });
        });

        /**
         * arrangement
         */
        $(window).on('post-hire-click', function(e, target) {
            var $target = $(target),
                id = $target.data('id'),
                container = $target.data('container');

            // set the data
            var data = {
                'post_id': id,
                'post_flag': '2'
            };

            // current url
            $currentURL = window.location.href.split('/')
            $currentURLLength = $currentURL.length;

            // send post request
            $.post('/ajax/post/update/'+id, data, function (response) {
                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);
                    // redirect
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);

                    return false;
                }

                // check if there's a result
                if (response.results) {
                    // show message
                    toastr.success('Post already Filled');
                    // remove the element

                    if ($currentURL[$currentURLLength - 1] != 'post-detail') {
                        var elemId = '#post-' + $(target).data('id');
                        $(elemId).remove();
                    }

                    window.location = '/post/search';
                }

                return false;
            });
        });

        /**
         * area city search
         */
        $(window).on('area-city-init', function(e, target) {
            target = $(target);

            //set data
            var data = {
                filter: {
                    area_type: 'city'
                },
                order: {
                    'area_name': 'ASC'
                },
                range: 0
            };

            function ajaxCall() {
                $.get('/ajax/area/search', data, function(response) {
                    if (response.error) {
                        setTimeout(ajaxCall, 3000);

                        return;
                    }

                    // add select province
                    $(target).append($('<option>')
                        .attr('value', '')
                        .html('Select Location'));

                    $.each(response.results, function(key, val) {
                        $(target).append($('<option>')
                            .attr('value', val.area_name)
                            .html(val.area_name));
                    });

                    // initialize chosen
                    $(target).chosen({
                        width: '100%',
                        placeholder_text_single: 'Select Location'
                    });
                }, 'json');
            }

            ajaxCall();
        });

    })();

    /**
     * Checkout UI
     */
    (function() {
        $(window).on('credit-calculator-init', function(e, target) {
            $('input[name="amount"]', target).change(function() {
                var amount = parseInt($(this).val().replace(/[^0-9]/g, ''));

                if(isNaN(amount)) {
                    amount = 1000;
                }

                if(amount < 100) {
                    amount = 100;
                }

                if(amount > 999999) {
                    amount = 999999;
                }

                var credits = amount;

                $('span.calculate-credit span', target).html(credits
                    .toFixed(0)
                    .replace(/./g, function(c, i, a) {
                        return i && c !== "." && ((a.length - i) % 3 === 0) ? ',' + c : c;
                    })
                );

                $('td.total span', target).html(amount
                    .toFixed(0)
                    .replace(/./g, function(c, i, a) {
                        return i && c !== "." && ((a.length - i) % 3 === 0) ? ',' + c : c;
                    })
                );

                $(this).val(amount);
            }).trigger('change');
        });

        $(window).on('payment-options-init', function(e, target) {
            $('input[name="number"]', target).change(function() {
                $('i.payment', target)
                    .removeClass('payment-visa')
                    .removeClass('payment-mastercard')
                    .removeClass('payment-discover')
                    .removeClass('payment-amex')
                    .removeClass('payment-cirrus')
                    .removeClass('payment-maestro')
                    .removeClass('payment-paypal')
                    .removeClass('payment-jcb')
                    .removeClass('payment-diners');

                $('i.payment', target).addClass(getCardType($(this).val().replace(/\s/, '')));
            }).trigger('change');
        });

        function getCardType(number) {
            // visa
            if (number.match(/^4/) != null) {
                return 'payment-visa';
            }

            // Mastercard
            if (number.match(/^5[1-5]/) != null) {
                return 'payment-mastercard';
            }

            // AMEX
            if (number.match(/^3[47]/) != null) {
                return 'payment-amex';
            }

            // Discover
            if (number.match(/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/) != null) {
                return 'payment-discover';
            }

            // Diners
            if (number.match(/^36/) != null) {
                return 'payment-diners';
            }

            // Diners - Carte Blanche
            if (number.match(/^30[0-5]/) != null) {
                return 'payment-diners';
            }

            // JCB
            if (number.match(/^35(2[89]|[3-8][0-9])/) != null) {
                return 'payment-jcb';
            }

            // Visa Electron
            if (number.match(/^(4026|417500|4508|4844|491(3|7))/) != null) {
                return 'payment-visa';
            }

            return '';
        }
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
     * Post
     */
    (function() {
        $(window).on('post-edit-modal-click', function(e, target) {
            var modal = $('#post-update'),
                name = $(target).parents('.left-wrapper')
                    .find('input[name="post_name"]').val(),
                position = $(target).parents('.left-wrapper')
                    .find('input[name="post_position"]').val(),
                location = $(target).parents('.left-wrapper')
                    .find('input[name="post_location"]').val(),
                experience = $(target).parents('.left-wrapper')
                    .find('input[name="post_experience"]').val();

            $(modal).find('input[name=post_name]').val(name);
            $(modal).find('input[name=post_position]').val(position);
            $(modal).find('input[name=post_location]').val(location);
            $(modal).find('select[name="post_experience"]').val(experience);

            $(modal).modal('show');
        });

        $(window).on('fix-height-init', function(e, target) {
            // Checks if there are post tags
            if ($('.similar-posts-container .post-item .post-tags').length) {
                $('.similar-posts-container .post-item').css('height', 400);
            }

            // Checks if there are two tips
            if ($('.post-tips-wrapper .col-md-6').length) {
                // Variable declaration
                var setHeight = 0;

                $('.post-tips-wrapper .col-md-6').each(function(i, tip) {
                    // Gets the containers' height
                    var checkHeight = $(tip).css('height').replace('px', '');
                    checkHeight = parseInt(checkHeight);

                    // Checks if the container height is larger than the previous
                    if (checkHeight > setHeight) {
                        setHeight = checkHeight;
                    }
                });

                // Overrides both heights
                $('.post-tips-wrapper .col-md-6').css('height', setHeight);
            }
        });

        $(window).on('renew-modal-click', function(e, target) {

            // Checks for the type
            var type = $(target).data('type');

            // Checks if this is a poster
            if (type == 'poster') {
                $('#renew-modal').find('.poster').removeClass('hide');
                $('.modal-content.flash .modal-body')
                    .css({"background": "url('/images/poster-removed.png')",
                    "background-repeat": "no-repeat", "background-size": "72%"});
            } else {
                // Assume that this is a seeker
                $('#renew-modal').find('.seeker').removeClass('hide');
                $('.modal-content.flash .modal-body')
                    .css({"background": "url('/images/seeker-removed.png')",
                    "background-repeat": "no-repeat", "background-size": "72%"});
            }

            $('#renew-modal').find('span.days-left').html($(target).data('days'));
            $('#renew-modal').find('span.post-name').html($(target).data('name'));
            $('#renew-modal').find('span.post-position').html($(target).data('position'));
            $('#renew-modal').find('span.post-location').html($(target).data('location'));
            $('#renew-modal').find('span.post-currenecy').html($(target).data('currenecy'));
            $('#renew-modal').find('span.post-salary').html($(target).data('salary'));
            $('#renew-modal').find('a').attr('data-id', $(target).data('id'));
            $('#renew-modal').modal('show');
            $('#renew-modal').doon();
            return false;
        });

        $(window).on('renew-expired-modal-click', function(e, target) {

            // Checks for the type
            var type = $(target).data('type');

            // Checks if this is a poster
            if (type == 'poster') {
                $('#renew-expired-modal').find('.poster').removeClass('hide');
                $('.modal-content.flash .modal-body')
                    .css({"background": "url('/images/poster-removed.png')",
                    "background-repeat": "no-repeat", "background-size": "72%"});
            } else {
                // Assume that this is a seeker
                $('#renew-expired-modal').find('.seeker').removeClass('hide');
                $('.modal-content.flash .modal-body')
                    .css({"background": "url('/images/seeker-removed.png')",
                    "background-repeat": "no-repeat", "background-size": "72%"});
            }
            $('#renew-expired-modal').find('span.days-left').html(Math.abs($(target).data('days')));
            $('#renew-expired-modal').find('span.post-name').html($(target).data('name'));
            $('#renew-expired-modal').find('span.post-position').html($(target).data('position'));
            $('#renew-expired-modal').find('span.post-location').html($(target).data('location'));
            $('#renew-expired-modal').find('span.post-currenecy').html($(target).data('currenecy'));
            $('#renew-expired-modal').find('span.post-salary').html($(target).data('salary'));
            $('#renew-expired-modal').find('a').attr('data-id', $(target).data('id'));
            $('#renew-expired-modal').find('a').attr('data-type', $(target).data('type'));
            $('#renew-expired-modal').modal('show');
            $('#renew-expired-modal').doon();
            return false;
        });

        $(window).on('expire-post-click', function(e, target) {
            var data = {};
            data.post_id = $(target).data('id');
            data.action = $(target).data('action');
            data.count = $(target).data('count');
            data.post_type = $(target).data('type');

            var url = '/ajax/post/renew';

            // for remove
            if (data.action === 1) {
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        toastr.error(response.message);
                        return;
                    }

                    // Assume there are no errors
                    $('.modal-content.main').hide(500);
                    $('.modal-content.renew').hide(500);


                    $('.modal-content.flash').show(1000);
                    toastr.success(response.message);
                    delayedRedirect('self', 4500);

                });
            }

            if (data.action === 0) {
                if (data.post_type === 'seeker') {
                    $.post(url, data, function(response) {
                        response = JSON.parse(response);

                        // Checks for errors
                        if (response.error) {
                            toastr.error(response.message);
                            return false;
                        }

                        // Assume there are no errors
                        $('.modal-content.main').hide(500);
                        $('.modal-content.renew').hide(500);

                        toastr.success(response.message);
                        delayedRedirect('self', 1500);
                    });
                } else {
                    if (data.count >= 5) {
                        var creditPay = $('input[name="credit_pay"]').val();

                        var redirect = '/post/renew/' + data.post_id;

                        $('#credit_modal').attr('data-redirect', redirect);

                        if (creditPay == 0) {
                            $('#credit_modal').modal('show');
                            $('.modal-content.main').hide(500);
                            $('.modal-content.renew').hide(500);
                        }
                    } else {
                        $.post(url, data, function(response) {
                            response = JSON.parse(response);

                            // Checks for errors
                            if (response.error) {
                                toastr.error(response.message);
                                return false;
                            }

                            // Assume there are no errors
                            $('.modal-content.main').hide(500);
                            $('.modal-content.renew').hide(500);

                            toastr.success(response.message);
                            delayedRedirect('self', 1500);
                        });
                    }
                }
            }

        });

        $(window).on('renew-confirm-click', function(e, target) {
            $('.modal-content.main').hide(250);
            $('.modal-content.flash').hide(250);
            $('.modal-content.renew').show(500);

        });


        $(window).on('hide.bs.modal.renew-modal', function (e, target) {
            $('.modal-content.main').show();
            $('.modal-content.renew').hide();
        });
    })();

    /**
     * Tooltip
     */
    $(window).on('tooltip-init', function(e, target) {
        $(target).tooltip();
    });

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

        $(window).on('toast-message-click', function(e, target) {
            var element = $(target),
            text = element.attr('data-text'),
            type = element.attr('data-type');
            switch(type) {
                case 'success':
                    toastr.success(text);
                break;
                case 'error':
                    toastr.error(text);
                break;
            }
        });
    })();

    /**
     * Research Section
     */
    (function() {
        $(window).on('first-rate-init', function(e, target) {
            $('.hiring-rate option').each(function(index, value) {
                var name = $(value).val();
                var min = 'P '+ $(value).data('min').toLocaleString();
                var max = 'P '+ $(value).data('max').toLocaleString();

                $('.salary-range h3').html(name);
                $('.salary-range .range-container .pull-left span').html(min);
                $('.salary-range .range-container .pull-right span').html(max);
                return false;
            });
        });

        $(window).on('select-industry-change', function(e, target) {
            var name = $('.hiring-rate').find(":selected").val();
            var min = 'P ' + $('.hiring-rate').find(":selected").data('min').toLocaleString();
            var max = 'P ' + $('.hiring-rate').find(":selected").data('max').toLocaleString();

            $('.salary-range h3').html(name);
            $('.salary-range .range-container .pull-left span').html(min);
            $('.salary-range .range-container .pull-right span').html(max);
        });
    })();

    /**
     * Form Section
     */
    (function() {
        // Events for the confirmation modal
        $(window).on('form-confirmation-submit', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');
            var modalClose = true;

            // Gets the type
            var type = $(target).data('type');
            var action = '';
            var module = 'form';

            // Checks on the type
            switch (type) {
                case 'create-form' :
                    action  = 'create';
                    module  = 'form';
                    success = 'created';
                    break;

                case 'publish-form' :
                    action  = 'publish';
                    module  = 'form';
                    success = 'published';
                    break;

                case 'delete-form' :
                    action  = 'remove';
                    module  = 'form';
                    success = 'deleted';
                    break;

                case 'bulk-delete-form' :
                    action  = 'bulk/remove';
                    module  = 'form';
                    success = 'deleted';
                    break;

                case 'bulk-delete-permanent-form' :
                    action  = 'bulk/permanent';
                    module  = 'form';
                    success = 'deleted';
                    break;

                case 'bulk-restore-form' :
                    action  = 'bulk/restore';
                    module  = 'form';
                    success = 'restored';
                    break;

                case 'duplicate-form' :
                    action  = 'duplicate';
                    module  = 'form';
                    success = 'duplicated';
                    break;

                case 'delete-question' :
                    action  = 'delete';
                    module  = 'question';
                    success = 'deleted';
                    break;

                    case 'remove-applicant' :
                    action  = 'attach/label';
                    module  = 'applicant';
                    success = 'removed';
                    break;

                case 'download-application' :
                    action  = 'download';
                    module  = 'applicant';
                    success = 'downloaded';
                    break;

                case 'enable-ats' :
                    action  = 'enable';
                    module  = 'ats';
                    success = 'enabled';
                    break;

                case 'attach-form' :
                    action  = 'attach/form';
                    module  = 'post';
                    success = 'attached';
                    break;

                case 'create-label' :
                    action  = 'label/create';
                    module  = 'applicant';
                    success = 'created';
                    break;

                case 'remove-label' :
                    action  = 'label/remove';
                    module  = 'label';
                    success = 'deleted';
                    break;

                case 'remove-applicant-label' :
                    action  = 'remove/label';
                    module  = 'applicant';
                    success = 'removed';
                    break;

                default :
                    action  = type;
                    module  = 'form';
                    break;
            }

            if (type == 'remove-applicant' && module == 'applicant') {
                var data = {
                    'applicant_id': $(target).data('id'),
                    'label_name': $(target).data('label'),
                };

                attachLabel(data);

                delayedRedirect('self', 1000);
                e.preventDefault();
                return;
            };

            // Constructs the ajax url
            var url  = '/ajax/' + module + '/' + action;

            // Checks if the module is for the form
            if (module == 'form') {
                var redirect  = '';
                var data = {};

                // Checks for the bulk remove action
                if (action == 'bulk/remove') {
                    var ids = [];

                    // Gets the ids to be deleted
                    $('.checkbox-single:checked').each(function(i, form) {
                        // Push the id into the array
                        ids.push($(form).val());
                    });

                    data.form_ids = ids;
                } else {
                    // Variable declaration
                    var id   = $(target).data('id');
                    var name = $(target).find('input[name="form_name"]').val();

                    // Checks if the id is not empty
                    if (id) {
                        data.form_id = id;
                    }

                    // Checks if the name is not empty
                    if (name) {
                        data.form_name = name;
                    }
                }

                if (action == 'bulk/permanent') {
                    var ids = [];

                    // Gets the ids to be deleted
                    $('.checkbox-single:checked').each(function(i, form) {
                        // Push the id into the array
                        ids.push($(form).val());
                    });

                    data.form_ids = ids;
                } else {
                    // Variable declaration
                    var id   = $(target).data('id');
                    var name = $(target).find('input[name="form_name"]').val();

                    // Checks if the id is not empty
                    if (id) {
                        data.form_id = id;
                    }

                    // Checks if the name is not empty
                    if (name) {
                        data.form_name = name;
                    }
                }

                // Checks for the bulk restore action
                if (action == 'bulk/restore') {
                    var ids = [];

                    // Gets the ids to be deleted
                    $('.checkbox-single:checked').each(function(i, form) {
                        // Push the id into the array
                        ids.push($(form).val());
                    });

                    data.form_ids = ids;
                } else {
                    // Variable declaration
                    var id   = $(target).data('id');
                    var name = $(target).find('input[name="form_name"]').val();

                    // Checks if the id is not empty
                    if (id) {
                        data.form_id = id;
                    }

                    // Checks if the name is not empty
                    if (name) {
                        data.form_name = name;
                    }
                }

                // sends the data via ajax
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        modalClose = false;
                        toastr.error(response.message);

                        // Allow submitting again
                        $(target).find('.btn.btn-default').removeAttr('disabled');
                        return;
                    }

                    // There are no errors at this point
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        // At this point there is no custom message
                        toastr.success('Form has been '+success+'!');
                    }

                    // Checks for type
                    switch (type) {
                        // Publish Form
                        case 'publish-form' :
                            redirect = '/profile/tracking/application/poster/search';
                            delayedRedirect(redirect);
                            break;

                        // Create Form
                        case 'create-form' :
                            redirect = '/profile/tracking/application/poster/update/' + response.results.form_id;
                            delayedRedirect(redirect);
                            break;

                        // Delete Form
                        case 'delete-form' :
                            deleteElement('form', id);
                            delayedRedirect(redirect);
                            break;

                        // Bulk Delete Form
                        case 'bulk-delete-form' :
                            deleteElement('form', response.ids);
                            delayedRedirect(redirect);
                            break;

                        // Bulk Delete Form
                       case 'bulk-delete-permanent-form' :
                            deleteElement('form', response.ids);
                            delayedRedirect(redirect);
                            break;

                        // Bulk Restore Form
                        case 'bulk-restore-form' :
                            deleteElement('form', response.ids);
                            delayedRedirect(redirect);
                            break;

                        // Duplicate Form
                        case 'duplicate-form' :
                            redirect = '/profile/tracking/application/poster/update/' + response.results.form_id;
                            delayedRedirect(redirect);
                            break;

                        default :
                            break;
                    }
                });
            }


            // Checks if the module is post
            if (module == 'post') {

                // Checks for the bulk remove action
                if (action == 'attach/form') {
                    // Variable declaration
                    var data = {};
                    var url = '/ajax/post/' + action;

                    data = {
                        'post_id' : $(target).data('post-id'),
                        'form_id' : $(target).data('form-id'),
                    };

                    //submit application form
                    $.ajax({
                        url: '/ajax/post/attach/form',
                        type: 'POST',
                        async: false,
                        data: { 'form_id': data.form_id, 'post_id': data.post_id },
                        success: function (response) {
                            if (response.error) {
                                toastr.error(response.message);
                            } else {
                                toastr.success(response.message);
                            }

                        }
                    });

                    delayedRedirect('self', 2500);
                }
            }

            // Checks if the module is ats
            if (module == 'ats') {
                // Variable declaration
                var data = {};
                var url = '/ajax/ats/' + action;

                data = {
                    'post_id' : $(target).data('post-id'),
                    'form_id' : $(target).data('form-id'),
                };

                // sends the data via ajax
                $.post(url, data, function(response) {
                    // Checks for errors
                    if (response.error) {
                        modalClose = false;
                        toastr.error(response.message);

                        // Allow submitting again
                        $(target).find('.btn.btn-default').removeAttr('disabled');

                        if (response.message == 'Insufficient-Credits') {
                            var redirect = encodeURIComponent(window.location.href);
                            window.location = '/profile/credit/checkout?redirect_uri' + redirect;
                        }

                        return;
                    }

                    // There are no errors at this point
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        // At this point there is no custom message
                        toastr.success('Form has been '+success+' for this post');
                    }

                    delayedRedirect('self', 2500);
                });
            }

            // Checks if the module is for the question
            if (module == 'question') {
                // Variable declaration
                var id   = $(target).data('id');
                var redirect  = '';
                var data = {};

                // Checks if the id is not empty
                if (id) {
                    data.question_id = id;
                }

                // sends the data via ajax
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        modalClose = false;
                        toastr.error(response.message);

                        // Allow submitting again
                        $(target).find('.btn.btn-default').removeAttr('disabled');

                        return;
                    }

                    // There are no errors at this point
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        // At this point there is no custom message
                        toastr.success('Question has been '+success+'!');
                    }

                    // Checks for type
                    switch (type) {
                        case 'delete-question' :
                            deleteElement('question', id);
                            break;
                    }
                });
            }

            // Checks if the module is applicant
            if (module == 'applicant') {
                // Variable declaration
                var data = {};
                var url = '/ajax/' + module + '/' + action;

                // Checks if the type is for creating a label
                if (type == 'create-label') {
                    data.label_name = $(target).find('input[name="label_name"]').val();
                } else {
                    data = {
                        'applicant_id' : $(target).data('id'),
                        'label_name' : $(target).data('label'),
                    };
                }

                // sends the data via ajax
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        modalClose = false;
                        toastr.error(response.message);

                        // Allow submitting again
                        $(target).find('.btn.btn-default').removeAttr('disabled');

                        return;
                    }

                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        // At this point there is no custom message
                        toastr.success('Applicant has been '+success+'!');
                    }

                    // There are no errors at this point
                    // Checks for the action
                    if (type == 'remove-applicant-label') {
                        var find = 'span[data-label="'+data.label_name+'"]';
                        deleteElement('applicant-detail-list', response.results[0].applicant_id, find);
                    }

                    // Checks for the action create label
                    if (type == 'create-label') {
                        delayedRedirect('self', 2500);
                    }

                    $(document.body).doon();
                });
            }

            // Checks if the module is label
            if (module == 'label') {
                // Variable declaration
                var data = {};
                var url = '/ajax/applicant/' + action;
                $(target).data('id');

                data = {
                    'applicant_id' : $(target).data('id'),
                    'label_name' : $(target).data('label'),
                };

                // Checks if the id is not empty
                if (typeof $(target).data('id') !== 'undefined') {
                    data.applicant_id = $(target).data('id');
                }

                // Checks if the label is not empty
                if (typeof $(target).data('label') !== 'undefined') {
                    data.label_name = $(target).data('label');
                }

                // sends the data via ajax
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        modalClose = false;
                        toastr.error(response.message);

                        // Allow submitting again
                        $(target).find('.btn.btn-default').removeAttr('disabled');

                        return;
                    }

                    // There are no errors at this point
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        // At this point there is no custom message
                        toastr.success('Label has been '+success+'!');
                    }

                    // Checks if we deleted a custom label
                    if (type =='remove-label') {
                        // Look for labels that exist with in the page
                        $('span[data-label="'+data.label_name+'"]').each(function(index, target) {
                            $(target).hide();

                            setTimeout(function() {
                                $(target).remove();
                            }, 1500);
                        });

                        // Look for li's with the custom label within the page and remove it
                        $('li[data-label="'+data.label_name+'"]').each(function(index, target) {
                            $(target).hide();

                            setTimeout(function() {
                                $(target).remove();
                            }, 1500);
                        });
                    }

                    $(document.body).doon();
                });
            }

            // Close the modal after 1 second
            setTimeout(function() {
                // Checks if we should close the modal
                if (modalClose) {
                    $('#confirmation-form-current').modal('hide');

                    // Remove the modal after 1 second
                    setTimeout(function() {
                        $('#confirmation-form-current').remove();
                    }, 1000);
                }
            }, 1000);

            e.preventDefault();
        });

        // Create Form
        $(window).on('form-add-question-click', function(e, target) {
            var modal = $('#form-custom-question').clone();
            var id = $(target).data('form-id');

            // Clone the modal
            modal.attr('id', 'form-custom-question-'+id);
            modal.find('input[name="form_id"]').val(id);
            modal.find('input[name="question_id"]').remove();
            modal.find('#form-custom')
                .attr('id', 'form-custom-'+id)
                .next()
                .attr('for', 'form-custom-'+id);
            modal.find('#form-file')
                .attr('id', 'form-file-'+id)
                .next()
                .attr('for', 'form-file-'+id);
            modal.find('form').data('id', id);

            // Reinitiate the doon
            modal.find('form').doon();

            // Show the modal
            modal.modal('show');
        });

        // Edit question to the form
        $(window).on('question-edit-click', function(e, target) {
            var id = $(target).data('id');
            var url = '/ajax/question/detail/' + id;

            // Get the detail of the question
            $.get(url, function(response) {
                response = JSON.parse(response);

                // Checks if there's an error
                if (response.error) {

                }

                // There are no errors at this point
                // Populate the question field
                var question = response.results;

                editQuestionForm(question);

            });
        });

        // Add question to the form
        $(window).on('form-add-question-submit', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');

            var id = $(target).data('id');
            var modal = $('#form-custom-question-' + id);
            var form = modal.find('form');
            var data = form.formToJson();

            // Pass the question to be saved
            $.post('/ajax/question/create', data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    // Shows the default message
                    toastr.error(response.message);

                    // Checks for specific errors
                    if ('errors' in response) {
                        // Loops through the errors
                        $.each(response.errors, function(index, value) {
                            toastr.error(value);
                        });
                    }

                    // Allow submitting again
                    $(target).find('.btn.btn-default').removeAttr('disabled');

                    return;
                }

                // There are no errors at this point
                toastr.success(response.message);

                // Populates the question to the form
                addQuestion(e, response.results);

                // Close the modal after 3 seconds
                setTimeout(function() {
                    modal.modal('hide');
                    // enable all inputs and buttons
                    $('input[name=question_file]').removeAttr('disabled');
                    $('input[name=question_custom]').removeAttr('disabled');
                    $('.form-answers input.form-control').removeAttr('disabled');
                    $('button[name=form-add-answer]').removeAttr('disabled');
                    $('.btn.form-publish').removeAttr('disabled');
                    // Remove the modal after 2 seconds
                    setTimeout(function() {
                        modal.remove();
                    }, 1000);
                    return window.location.reload();
                }, 1000);

                return;
            });

            e.preventDefault();
        });

        // Edit question of the form
        $(window).on('form-edit-question-submit', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');

            var id = $(target).data('id');
            var modal = $('#form-custom-question-' + id);
            var form = modal.find('form');
            var data = form.formToJson();

            // Pass the question to be updated
            $.post('/ajax/question/update', data, function(response) {
                response = JSON.parse(response);

                // Allow submitting again
                $(target).find('.btn.btn-default').removeAttr('disabled');

                // Checks for errors
                if (response.error) {
                    // Shows the default message
                    toastr.error(response.message);

                    // Checks for specific errors
                    if ('errors' in response) {
                        // Loops through the errors
                        $.each(response.errors, function(index, value) {
                            toastr.error(value);
                        });
                    }

                    // Allow the form to be submitted again
                    $(target).find('.btn.btn-default').removeAttr('disabled');

                    return;
                }

                // There are no errors at this point
                toastr.success(response.message);

                // Delete the previous question
                deleteElement('question', response.results.question_id);

                // Add the newly updated question
                setTimeout(function() {
                    // Populates the question to the form
                    addQuestion(e, response.results);
                }, 1000);

                // Close the modal after 3 seconds
                setTimeout(function() {
                    modal.modal('hide');
                    // enable all inputs and buttons
                    $('input[name=question_file]').removeAttr('disabled');
                    $('input[name=question_custom]').removeAttr('disabled');
                    $('.form-answers input.form-control').removeAttr('disabled');
                    $('button[name=form-add-answer]').removeAttr('disabled');

                    // Remove the modal after 2 seconds
                    setTimeout(function() {
                        modal.remove();
                    }, 1000);
                }, 1000);

                return;
            });

            e.preventDefault();
        });

        // Shows the edit form name fields
        $(window).on('form-name-change-click', function(e, target) {
            $('.form-name-display').hide(500);
            $('.form-name-input').show(500);
        });

        // Cancel the event for updating the form name
        $(window).on('form-title-edit-cancel-click', function(e, target) {
            $('.form-name-display').show(500);
            $('.form-name-input').hide(500);

            // Overwrite the input value in case the user changed it
            var formName = $('.form-name-display span').html();
            $('.form-name-input input').val(formName);
        });

        // Updates the form name
        $(window).on('form-title-edit-update-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');

            // Variable Declarations
            var id = $(target).data('id');
            var name = $(target).prev().val();
            var data = {
                'form_id'   : id,
                'form_name' : name
            };

            // Sends the data to be updated
            $.post('/ajax/form/update', data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    toastr.error(response.message);

                    // Allow submitting again
                    $(target).find('.btn.btn-default').removeAttr('disabled');
                    return;
                }

                // There are no errors at this point
                // Checks if there is a custom message
                if (typeof response.message !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    // At this point there is no custom message
                    toastr.success('Form has been '+success+'!');
                }

                // Lets update the html
                $('.form-name-display span').html(response.results.form_name);
                $('.form-name-display').show(500);
                $('.form-name-input').hide(500);

                // Allow submitting again
                $(target).find('.btn.btn-default').removeAttr('disabled');
            });

            e.preventDefault();
        });

        // Delete Form Message
        $(window).on('form-delete-click', function(e, target) {
            var data = {
                'title'    : 'Delete Form',
                'subtitle' : $(target).data('title'),
                'message'  : 'Are you sure you want to delete this form?',
                'action'   : 'Delete',
                'type'     : 'delete-form',
                'id'       : $(target).data('id'),
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Bulk Restore Form Message
        $(window).on('form-bulk-delete-click', function(e, target) {
            var data = {
                'title'    : 'Delete Forms',
                'message'  : 'Are you sure you want to delete these forms?',
                'action'   : 'Delete',
                'type'     : 'bulk-delete-form',
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Delete Form Message
        $(window).on('form-duplicate-click', function(e, target) {
            var data = {
                'title'    : 'Duplicate Form',
                'subtitle' : $(target).data('title'),
                'message'  : 'Are you sure you want to duplicate this form?',
                'action'   : 'Duplicate',
                'type'     : 'duplicate-form',
                'id'       : $(target).data('id'),
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Delete Form Message
        $(window).on('form-bulk-restore-click', function(e, target) {
            var data = {
                'title'    : 'Restore Forms',
                'message'  : 'Are you sure you want to restore these forms?',
                'action'   : 'Restore',
                'type'     : 'bulk-restore-form',
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Bulk Delete Permanent Form Message
        $(window).on('form-permanent-remove-click', function(e, target) {
            var data = {
                'title'    : 'Delete Forms Permanently',
                'message'  : 'Are you sure you want to delete these forms permanently?',
                'action'   : 'Delete',
                'type'     : 'bulk-delete-permanent-form',
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Publish Form Message
        $(window).on('form-publish-click', function(e, target) {
            if ($(target).attr('disabled')) {
                toastr.error('Error publishing form');
                return false;
            }

            // Let's disable the button from submitting again
            $(target).attr('disabled', 'disabled');

            var data = {
                'title'   : 'Publish Form',
                'message' : 'Are you sure you want to publish this form?',
                'action'  : 'Publish',
                'type'    : 'publish-form',
                'id'      : $(target).data('id'),
            };

            // Call to populate modal
            confirmationContent(e, data);
        });

        // Preview Form
        $(window).on('form-preview-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).attr('disabled', 'disabled');

            var id = $(target).data('id');
            button = $(target);

            button.css('pointer-events','none');
            // get the form and questions
            $.get('/ajax/form/preview/' + id, function(response) {
                // Checks if there are no errors
                if (!response.error) {
                    var form = response.results.form;
                    var questions = response.results.questions;

                    if (form && questions) {
                        // Populate the form via JS
                        previewForm(button, form, questions, 'form-preview');
                        toastr.success('Generating Preview');
                    }
                }
            });

            // Allow the preview button to be clicked after 3 seconds
            setTimeout(function() {
                $(target).removeAttr('disabled');
            }, 3000);
        });

        // Delete Question Message
        $(window).on('question-delete-click', function(e, target) {
            var data = {
                'title'   : 'Delete Question',
                'message' : 'Are you sure you want to delete this question?',
                'action'  : 'Delete',
                'type'    : 'delete-question',
                'id'      : $(target).data('id'),
            };

            // Call to populate modal
            confirmationContent(e, data);
        });

        // Append Confirmation Model
        $(window).on('confirm-content-click', function(e, target) {
            var data = {
                'title'    : $(target).data('title'),
                'subtitle' : $(target).data('subtitle'),
                'message'  : $(target).data('message'),
                'action'   : $(target).data('action'),
                'type'     : $(target).data('type'),
                'id'       : $(target).data('id'),
                'label'    : $(target).data('label-name'),
            };

            // Call to populate modal
            confirmationContent(e, data);
        });

        // Enable ATS Model
        $(window).on('enable-ats-click', function(e, target) {
            var data = {
                'title'    : $(target).data('title'),
                'subtitle' : $(target).data('subtitle'),
                'message'  : $(target).data('message'),
                'action'   : $(target).data('action'),
                'type'     : $(target).data('type'),
                'post_id'       : $(target).data('post-id'),
                'form_id'       : $(target).data('form-id'),
                'label'    : $(target).data('label-name'),
            };
            // Call to populate modal
            confirmationContent(e, data);
        });

        // Add the question to the form
        var addQuestion = function(e, data) {
            // Clones the default cointainer
            var questionElement = $('.question-item.hide').clone();
            questionElement.doon();

            var elemId = 'question-'+data.question_id;
            questionElement.attr('id', elemId).removeClass('hide').hide();
            questionElement.find('.question-name-input').val(data.question_name);
            questionElement.find('.question-name-display').html(data.question_name);

            // Adds the ids to the event triggers
            questionElement.find('.question-edit').attr('data-id', data.question_id);
            questionElement.find('.question-delete').attr('data-id', data.question_id);

            // Checks if choices are allowed
            if (data.question_choices.length !== 0) {
                // Shows the choices container
                questionElement.find('.question-choices.hide')
                    .removeClass('hide');

                // Loops through the choices
                $.each(data.question_choices, function(i, choice) {
                    var answerName       = 'answer_choices[' + data.question_id + ']';
                    var questionContainer = questionElement.find('.question-container.hide').clone();
                    questionContainer.removeClass('hide');

                    questionContainer
                        .find('.question-choice.hide')
                        .removeClass('hide')
                        .attr('name', answerName)
                        .attr('id', data.question_id+'-question-'+i)
                        .val(choice);

                    questionContainer
                        .find('.question-label.hide')
                        .removeClass('hide')
                        .attr('for', data.question_id+'-question-'+i)
                        .html(choice);

                    // Append the question to the form
                    questionElement.find('.question-choices')
                        .append(questionContainer);
                });
            }

            // Checks if custom answers are allowed
            if ($.inArray('custom', data.question_type) !== -1) {
                questionElement.find('.question-custom')
                    .removeAttr('disabled')
                    .removeClass('hide');
            }

            // Checks if file answers are allowed
            if ($.inArray('file', data.question_type) !== -1) {
                questionElement.find('.question-file')
                    .removeAttr('disabled')
                    .removeClass('hide');
            }

            // Checks if there is a no results element
            if ($('.no-results').length) {
                $('.no-results').hide(500);
            }

            $('.question-list').append(questionElement);
            questionElement.show(500);
            return;
        }

        // Populates the question form to be edited
        var editQuestionForm = function(data) {
            var modal = $('#form-custom-question').clone();

            // Clone the modal
            modal.attr('id', 'form-custom-question-'+data.form_id);
            modal.find('input[name="form_id"]').val(data.form_id);
            modal.find('input[name="question_id"]').val(data.question_id);
            modal.find('#form-custom')
                .attr('id', 'form-custom-'+data.form_id)
                .next()
                .attr('for', 'form-custom-'+data.form_id);
            modal.find('#form-file')
                .attr('id', 'form-file-'+data.form_id)
                .next()
                .attr('for', 'form-file-'+data.form_id);
            modal.find('form').attr('data-id', data.form_id)
                .attr('data-do', 'form-edit-question');

            // Question Name / question_name
            modal.find('input[name="question_name"]').val(data.question_name);

            // Checks if there are choices
            if (data.question_choices.length !== 0) {
                // Empties the previous choices
                modal.find('.form-answers').html('');

                // Loops through the question_choices
                $.each(data.question_choices, function(i, choice) {
                    // Clone the answer element
                    var questionAnswer = modal.find('.form-answer.hide').clone();

                    // Show the element
                    questionAnswer.removeClass('hide');

                    // Alter Elements of the choices
                    questionAnswer.find('input').attr('name', 'question_choices[]').val(choice);
                    modal.find('.form-answers').append(questionAnswer);
                });
                // if there are answers disable the request-file checkbox
                modal.find('#form-file-'+data.form_id).attr('disabled', 'disabled');
            }

            // Checks if custom answers are allowed
            if ($.inArray('custom', data.question_type) !== -1) {
                // Show the input field / question_custom
                modal.find('#form-custom-'+data.form_id).removeAttr('disabled');
                modal.find('#form-custom-'+data.form_id).attr('checked', 'checked');
                // remove any disabled fields
                modal.find('button[name=form-add-answer]').removeAttr('disabled');
                modal.find('.form-answers input.form-control').removeAttr('disabled');
                // except request-file
                modal.find('#form-file-'+data.form_id).attr('disabled', 'disabled');
            }

            // Checks if file answers are allowed
            if ($.inArray('file', data.question_type) !== -1) {
                // Show the input field / question_file
                modal.find('#form-file-'+data.form_id).removeAttr('disabled');
                modal.find('#form-file-'+data.form_id).attr('checked', 'checked');
                // disable all
                modal.find('#form-custom-'+data.form_id).attr('disabled', 'disabled');
                modal.find('button[name=form-add-answer]').attr('disabled', 'disabled');
                modal.find('.form-answers input.form-control').attr('disabled', 'disabled');
            }

            // Reinitiate the doon
            modal.find('form').doon();

            // Show the modal
            modal.modal('show');
        }

        // Reusable function to populate and show ATS confirmation modal
        var confirmationContent = function(e, data) {
            // Gets the modal
            var modal = $('#confirmation-form').clone();
            modal.doon();
            modal.attr('id', 'confirmation-form-current');

            // Populates the data
            modal.find('.modal-title').html(data.title);
            modal.find('.modal-message').html(data.message);
            modal.find('.btn.btn-default').html(data.action);
            modal.find('.modal-form').attr('data-type', data.type);

            // Checks if there is a subtitle
            if (typeof data.subtitle !== 'undefined') {
                modal.find('.sub-title')
                    .removeClass('hide')
                    .html(data.subtitle)
            }

            // Checks if this is a form create
            if (data.type == 'create-form') {
                modal.find('.form-name.hide').removeClass('hide');
                modal.find('.form-name input').removeAttr('disabled');
            }

            // Checks if this is a label create
            if (data.type == 'create-label') {
                modal.find('.label-name.hide').removeClass('hide');
                modal.find('.label-name input').removeAttr('disabled');
            }

            if (typeof data.id !== 'undefined') {
                modal.find('.modal-form').attr('data-id', data.id);
            }

            if (typeof data.post_id !== 'undefined') {
                modal.find('.modal-form').attr('data-post-id', data.post_id);
            }

            if (typeof data.form_id !== 'undefined') {
                modal.find('.modal-form').attr('data-form-id', data.form_id);
            }

            if (typeof data.label !== 'undefined') {
                modal.find('.modal-form').attr('data-label', data.label);
            }

            // Shows the modal
            modal.modal('show');
            e.preventDefault();
        }

        // Events for the custom questions
        // Add more answer boxes
        $(window).on('form-add-answer-click', function(e, target) {
            var parent = $(target).parent();
            var answer = parent.find('.form-answer.hide').clone();
            answer.removeClass('hide');
            parent.find('.form-answers').append(answer);
            $(document.body).doon();

            return false;
        });

        // Remove answer box
        $(window).on('form-remove-answer-click', function(e, target) {
            // Removes the parent element which holds the button
            $(target).parent().remove();
            return false;
        });

        $(window).on('form-check-custom-change', function(e, target) {
            var elemId = '.custom-' + $(target).data('question');

            // Check if there is any input at the custom text field
            if ($(elemId).val() !== '') {
                // Empties the text input
                $(elemId).val('');
            }
        });

        $(window).on('form-check-choices-input', function(e, target) {
            var elemId = '.choice-' + $(target).data('question');

            // Loops through the radio buttons
            $(elemId).each(function(index, value) {
                // Checks if a radio button is ticked
                if ($(value).is(':checked')) {
                    // Remove the check
                    $(value).prop('checked', false);
                }
            });
        });

        // Applicant Form Submit
        $(window).on('applicant-form-submit', function(e, target) {
            // Remove the data-on to prevent re-submitting the form
            $(target).attr('data-on', '');

            // Adds a signifier that the form is being processed
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');
            $(target).find('.btn.btn-default').html('Submitting <i class="fa fa-spinner fa-pulse"></i>');

            // Default value for errors
            var errors = false;
            var data = $(target).formToJson();

            // Gets the total number of questions
            var totalQuestion = $('input[name="question_name"]').length;
            var answered = 0;

            // Loops through the choices answers
            $('input.question-choice').each(function() {
                // Checks if there is an answer
                if ($(this, 'name["'+ $(this).attr('name') +'"]').is(':checked')) {
                    var elemId = $(this).data('question');
                    $('input.question-custom[data-question="' + elemId + '"]').attr('disabled', 'disabled');
                    answered++;
                }
            });

            // Loops through the custom answers
            $('input.question-custom').each(function() {
                // Checks for custom answer
                if ($(this).val() != '') {
                    answered++;
                }
            });

            // Loops through the file answers
            $('.file-answer').each(function() {
                // Checks for uploaded file
                if ($(this).val() != '' && $(this).attr('size') <= 2000000) {
                    answered++;
                }

                if($(this).attr('size') > 2000000) {
                    // Checks if the file is too big
                    toastr.error('File must be less than 2mb');
                    errors = true;
                }
            });

            // Compares if the form was not fully answered
            if (answered != totalQuestion) {
                toastr.error('Please complete the form');
                errors = true;
            }

            // Checks for errors
            if (errors) {
                $('input.question-custom').removeAttr('disabled');

                $(target).attr('data-on', 'submit');
                $(target).find('.btn.btn-default').removeAttr('disabled')
                    .html('Submit');
                return false;
            }

            return true;
        });

        // Disable the form choices and custom answer when question-file is checked
        $(window).on('form-file-click', function (e, target) {
            // clone the modal so not to override all the input
            var id = $('.form-custom-question.in').find('form').data('id');
            var element = '#form-custom-question-'+id;

            if ($(element).find('input[name=question_file]').is(':checked')) {
                // remove input values
                $(element).find('.form-answers input.form-control').val('');
                // disable boxes
                $(element).find('.form-answers input.form-control').attr('disabled', 'disabled');

                // remove checked attribute of custom answer
                $(element).find('input[name=question_custom]').removeAttr('checked');
                // disable button
                $(element).find('input[name=question_custom]').attr('disabled', 'disabled');

                // disable the [add answer] button
                $(element).find('button[name=form-add-answer]').attr('disabled', 'disabled');
            } else {
                // enable choices input boxes
                $(element).find('.form-answers input.form-control').removeAttr('disabled');

                // enable custom answer checkbox
                $(element).find('input[name=question_custom]').removeAttr('disabled');

                // enable the [add answer] button
                $(element).find('button[name=form-add-answer]').removeAttr('disabled');
            }
        });

        // Disable the form file when custom answer is checked
        $(window).on('form-custom-answer-click', function (e, target) {
            // clone the modal so not to override all the input
            var id = $('.form-custom-question.in').find('form').data('id');
            var element = '#form-custom-question-'+id;

            if ($(element).find('input[name=question_custom]').is(':checked')) {
                // disable button
                $(element).find('input[name=question_file]').attr('disabled', 'disabled');
            } else {
                // before enabling request-file check if there are any values
                // in the choices
                var isEmpty = true;
                $(element).find('.form-answer:visible input[name^=question_choices]').each(function (index, value) {
                    if ($.trim($(value).val()) != '') {
                        isEmpty = false;
                    }
                });
                if (isEmpty == false) {
                    $(element).find('input[name=question_file]').attr('disabled', 'disabled');
                } else {
                    $(element).find('input[name=question_file]').removeAttr('disabled');
                }
            }
        });

        // Disable the request-file checkbox when choices have been filled out
        $(window).on('form-choices-keyup', function (e, target) {
            // clone the modal so not to override all the input
            var id = $('.form-custom-question.in').find('form').data('id');
            var element = '#form-custom-question-'+id;

            var isEmpty = true;
            $(element).find('.form-answer:visible input[name^=question_choices]').each(function (index, value) {
                if ($.trim($(value).val()) != '') {
                    isEmpty = false;
                }
            });
            if (isEmpty == false) {
                $(element).find('input[name=question_file]').attr('disabled', 'disabled');
            } else {
                // before enabling request-file check if custom answer is checked
                if ($(element).find('input[name=question_custom]').is(':checked')) {
                    $(element).find('input[name=question_file]').attr('enabled', 'enabled');
                } else {
                    $(element).find('input[name=question_file]').removeAttr('disabled');
                }
            }
        });

        $(window).on('hide.bs.modal', function (e) {
            // Checks if the modal is open
            if ($('.form-publish').length) {
                $('.form-publish').removeAttr('disabled');
            }
        });
    })();

    /**
     * Interview Scheduler Section
     */
    (function() {
        // Alters the date picker then shows it
        $(window).on('landing-datepicker-init', function(e, target) {
            var dates = [];

            // Gets the list of dates to block
            $.get('/ajax/interview/availability/list', function(response) {
                response = JSON.parse(response);

                // Checks if there were no errors
                if (!response.error) {
                    var dates = response.results;

                    $(target).datepicker({
                        beforeShowDay: function(date){
                            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                            return [ dates.indexOf(string) == -1 ]
                        }
                    });
                } else {
                    $(target).datepicker({});
                }
            });
        });

        // Events for the confirmation modal
        $(window).on('interview-confirmation-submit', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');
            var modalClose = true;
            var data = {};

            // Gets the type
            var type = $(target).data('type');
            var tag = $(target).data('tag');
            var action = '';
            var module = 'interview';
            var id = $('#confirmation-interview-current').find('form').data('id');

            switch (type) {
                case 'interview-setting-delete' :
                    action = 'availability/remove';
                    break;

                case 'interview-schedule-delete' :
                    action = 'schedule/remove';
                    break;

                case 'interview-tag' :
                    action = 'schedule/tag';
                    break;
            }

            // Constructs the ajax url
            var url  = '/ajax/' + module + '/' + action;

            // Checks if there is an id
            if (typeof id !== 'undefined') {
                url += '/' + id;
            }

            if (typeof tag !== 'undefined') {
                data.tag = tag;
            }

            // sends the data via ajax
            $.post(url, data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    modalClose = false;
                    toastr.error(response.message);

                    // Allow submitting again
                    $(target).find('.btn.btn-default').removeAttr('disabled');
                    return;
                }

                // There are no errors at this point
                // Checks for the module
                if (module == 'interview') {
                    // Checks for the type
                    switch (type) {
                        case 'interview-setting-delete' :
                            deleteElement('interview-detail', id);
                            break;

                        case 'interview-schedule-delete' :
                            deleteElement('interview-schedule', id);
                            deleteElement('profile-schedule', id);

                            // Update the slots
                            var elemId = '#date-detail-'+response.setting;
                            var slotsTaken = parseInt($(elemId).find('.slots-taken').html());
                            slotsTaken -= 1;
                            $(elemId).find('.slots-taken').html(slotsTaken);
                            break;

                        case 'interview-tag' :
                            var elemId = '#interview-schedule-'+id;
                            $(elemId).find('.pending').removeClass('pending');

                            // Deletes the cooresponding elements
                            deleteElement('interview-schedule', id, '.fa-exclamation-circle');
                            deleteElement('interview-schedule', id, 'button.dropdown-toggle');
                            deleteElement('interview-schedule', id, 'ul.schedule-dropdown');

                            // Updates the status
                            $(elemId).find('.status-pending').html(tag);
                            break;

                        default :
                            break;
                    }

                    // Sends a success message
                    toastr.success(response.message);
                }
            });

            // Close the modal after 1 second
            setTimeout(function() {
                // Checks if we should close the modal
                if (modalClose) {
                    $('#confirmation-interview-current').modal('hide');

                    // Remove the modal after 1 second
                    setTimeout(function() {
                        $('#confirmation-interview-current').remove();
                    }, 1000);
                }
            }, 1000);

            return false;
        });

        // Append Confirmation Model
        $(window).on('interview-content-click', function(e, target) {
            var data = {
                'title'    : $(target).data('title'),
                'subtitle' : $(target).data('subtitle'),
                'message'  : $(target).data('message'),
                'action'   : $(target).data('action'),
                'type'     : $(target).data('type'),
                'tag'      : $(target).data('tag'),
                'id'       : $(target).data('id'),
            };

            // Call to populate modal
            interviewModalContent(e, data);
        });

        // Gets the profiles that have liked this post
        $(window).on('post-likes-change', function(e, target) {
            // Disable changing the select while we run this
            $(target).attr('disabled', 'disabled');

            var post = $(target).find(':selected').val();

            // Check if the post is not numeric
            // To avoid the Select a Job option from causing errors
            if (!$.isNumeric(post)) {
                $(target).removeAttr('disabled');
                return false;
            }

            // Lets get the applicants for this post
            // Based on the post id// sends the data via ajax
            $.get('/ajax/interview/post/'+post+'/likers', function(response) {
                response = JSON.parse(response);

                // Disable selecetion of Applicants and Schedules
                $('.profile-list select').attr('disabled', 'disabled');
                $('input[name="interview_setting_id"]').attr('disabled', 'disabled');

                // Checks for errors
                if (response.error) {
                    toastr.success(response.message);

                    // Allow submitting again
                    $(target).removeAttr('disabled');
                    return false;
                }

                // There are no errors at this point
                // Disable the list first
                $('.profile-list select').attr('disabled', 'disabled');

                // Lets show a generating message first
                toastr.info(
                    'Fetching Applicants <i class="fa fa-spinner fa-pulse"></i>',
                    'New Message',
                    { timeOut: 1500 }
                );

                // We need to replace the current options
                var options = '<option>Select Applicant</option>';

                // Loops through the profiles returned
                $.each(response.profiles, function(index, value) {
                    var option = '<option value="'+value.profile_id+'">'+value.profile_name+'</option>';
                    options += option;
                });

                // Let's put a downtime
                setTimeout(function() {
                    // Overrides the current option
                    $('.profile-list select').html(options);

                    // Removes the disables
                    $(target).removeAttr('disabled');

                    // Checks if the profile list is not empty
                    if(options != '') {
                        $('.profile-list select').removeAttr('disabled');
                    }
                }, 2000);

                // Replace the options
                return false;
            });
        });

        $(window).on('interview-applicants-change', function(e, target) {
            var id = $(target).val();

            // Checks if an id was returned
            if (typeof id !== 'undefined') {
                // Removes the disabled property for the date time selector
                $('.list-selector').removeAttr('disabled')
                    .attr('data-toggle', 'dropdown');
                $('input[name="interview_setting_id"]').removeAttr('disabled');
            }

            return false;
        });

        // Selecting interview dates
        $(window).on('select-interview-setting-click', function(e, target) {
            // Removes the active from the other classes
            $('.dropdown-menu li a.active').removeClass('active');

            var id = $(target).data('id');
            var selector = '#setting-detail-'+id;
            var detail = $(selector);

            $(target).addClass('active');
            $('input[name="interview_setting_id"]').val(id);

            $('.setting-selected').find('.setting-date span.slots')
                .html(detail.find('.setting-date span.slots').html());

            $('.setting-selected').find('.setting-date span.date')
                .html(detail.find('.setting-date span.date').html());

            $('.setting-selected').find('.setting-time span')
                .html(detail.find('.setting-time span').html());

            $('.setting-selected').find('input[name="slots_taken"]')
                .val(detail.find('input[name="slots_taken"]').val());

            $('.setting-selected').find('input[name="max_slots"]')
                .val(detail.find('input[name="max_slots"]').val());

            $('.dropdown-list.open').removeClass('open');
            $('.dropdown-list').find('.dropdown-toggle').attr('aria-expanded', 'false')

            e.preventDefault();
            return false;
        });

        // Schedule interview
        $(window).on('schedule-interview-click', function(e, target) {
            var data = $('form.schedule-form').formToJson();

            // Checks if the profile is missing
            switch (true) {
                case (typeof data.profile_id === 'undefined') :
                case (!$.isNumeric(data.profile_id)) :
                case (!$.isNumeric(data.post_id)) :
                case (!$.isNumeric(data.interview_setting_id)) :
                    toastr.error('Please complete the form to schedule an interview');
                    return false;
                    break;

                default :
                    break;
            }

            // Scheduling Interview notifier
            toastr.info(
                'Processing Interview Schedule <i class="fa fa-spinner fa-pulse"></i>',
                'Please Wait',
                { timeOut: 1500 }
            );

            // At this point, we can now schedule an interview
            // Let's disable the button from submitting again
            $(target).attr('disabled', 'disabled');
            $(target).html('Scheduling <i class="fa fa-spinner fa-pulse"></i>');

            // Constructs the ajax url
            var url = '/ajax/interview/schedule';

            setTimeout(function() {
                // Sends out the post data
                $.post(url, data, function(response) {
                    response = JSON.parse(response);

                    // Checks for errors
                    if (response.error) {
                        // Allow Scheduling button
                        $(target).removeAttr('disabled');
                        $(target).html('Schedule');

                        toastr.error(response.message);

                        return false;
                    }

                    // At this point, there are no errors
                    // Let's delete the Applicant from the list
                    deleteElement('applicant-profile-list', 0, 'option[value="'+data.profile_id+'"]');

                    // Updates the slots
                    var slotsHTML = '<i class="fa fa-user"></i> &nbsp';
                    var newSlots = parseInt($('.setting-selected input[name="slots_taken"]').val()) + 1;
                    slotsHTML += newSlots + '/' +
                        ($('.setting-selected input[name="max_slots"]').val());
                    $('.setting-selected .slots').html(slotsHTML);
                    $('.setting-selected input[name="slots_taken"]').val(newSlots)

                    // Update the selector as well
                    var selector = '#setting-detail-' + data.interview_setting_id;
                    selector = $(selector);
                    selector.find('input[name="slots_taken"]').val(newSlots);
                    selector.find('.slots').html(slotsHTML);

                    // Sends a success message
                    toastr.success(response.message);

                    // Allow the submit button again after 2.5 seconds
                    setTimeout(function() {
                        $(target).html('Schedule');
                        $(target).removeAttr('disabled');
                    }, 2500);
                });
            }, 1500);

            e.preventDefault();
            return false;
        });

        // Show the availability add modal
        $(window).on('availability-add-modal-click', function(e, target) {
            var clone = $('#interview-availability-add').clone(true);
            clone.attr('id', 'interview-availability-add-clone');

            // Gets the list of dates to block
            $.get('/ajax/interview/availability/list', function(response) {
                response = JSON.parse(response);

                // Checks if there were no errors
                if (!response.error) {
                    var dates = response.results;

                    clone.find('.datepicker')
                        .removeClass('hasDatepicker')
                        .removeAttr('id')
                        .datepicker({
                            beforeShowDay: function(date){
                                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                return [ dates.indexOf(string) == -1 ]
                            },
                            inline: true,
                            showOtherMonths: true,
                            changeMonth: true,
                            changeYear: true,
                            dayNamesMin: ['S', 'M', 'T', 'W', 'Th', 'F', 'S'],
                            dateFormat: 'MM dd, yy',
                            yearRange: "-100:+0",
                        }
                    );
                }
            });

            clone.doon();
            clone.modal('show');
            e.preventDefault();
        });

        // Show the availability edit modal
        $(window).on('availability-edit-modal-click', function(e, target) {
            var clone = $('#interview-availability-edit').clone(true);
            clone.attr('id', 'interview-availability-edit-clone');

            // Gets the list of dates to block
            $.get('/ajax/interview/availability/list', function(response) {
                response = JSON.parse(response);

                // Checks if there were no errors
                if (!response.error) {
                    var dates = response.results;

                    clone.find('.datepicker')
                        .removeClass('hasDatepicker')
                        .removeAttr('id')
                        .datepicker({
                            beforeShowDay: function(date){
                                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                return [ dates.indexOf(string) == -1 ]
                            },
                            inline: true,
                            showOtherMonths: true,
                            changeMonth: true,
                            changeYear: true,
                            dayNamesMin: ['S', 'M', 'T', 'W', 'Th', 'F', 'S'],
                            dateFormat: 'MM dd, yy',
                            yearRange: "-100:+0",
                        }
                    );
                }
            });

            clone.doon();
            clone.modal('show');

            clone.find('.datepicker').val($(target).data('setting-date'));
            clone.find('input[name="interview_setting_id"]').val($(target).data('id'));
            clone.find('input[name="slots"]').val($(target).data('setting-slots'));
            clone.find('input[name="start_time"]').val($(target).data('setting-start'));
            clone.find('input[name="end_time"]').val($(target).data('setting-end'));

            e.preventDefault();
        });

        // Add the availability
        $(window).on('availability-add-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');

            var data = $('#interview-availability-add-clone.in').find('form').formToJson();
            var url = '/ajax/interview/availability/add';

            // sends the data via ajax
            $.post(url, data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    modalClose = false;
                    toastr.error(response.message);

                    // Checks if dates were returned
                    if (typeof response.dates !== 'undefined') {
                        var message = 'These dates have already been taken: <br/>'

                        // Loops through the dates
                        $.each(response.dates, function(index, value) {
                            message += value += '<br/>';
                        });

                        setTimeout(function() {
                            toastr.error(message);
                        }, 1000);
                    }

                    // Checks if errors were returned
                    if (typeof response.errors !== 'undefined') {
                        // Loops through the errors
                        $.each(response.errors, function(index, value) {
                            toastr.error(value);
                        });
                    }

                    // Allow submitting again
                    $(target).find('.btn.btn-default').removeAttr('disabled');
                    return;
                }

                // There are no errors at this point
                toastr.info(
                    'Adding new availabilities <i class="fa fa-spinner fa-pulse"></i>',
                    'Please Wait',
                    { timeOut: 1500 }
                );

                // Checks for the empty shcedules message
                if ($('.empty-list').length) {
                    $('.empty-list').remove();
                }

                if ($('.interview-setting-list').css('min-height') == '0px') {
                    $('.interview-setting-list').css('min-height', '120px');
                }

                // Loops through the results
                $.each(response.results, function(index, value) {
                    var detail = $('.interview-setting-detail.hide').clone();
                    var id = 'interview-detail-'+value.id;

                    detail.removeClass('hide');
                    detail.attr('id', id);
                    detail.find('.setting-slots span').html(value.slots);
                    detail.find('.setting-date span').html(value.date);
                    detail.find('.setting-time span')
                        .html(value.start_formated+' - '+value.end_formated);

                    // Adds the data for edit button
                    detail.find('a.availability-edit')
                        .attr('data-id', value.id)
                        .attr('data-setting-date', value.date)
                        .attr('data-setting-start', value.start)
                        .attr('data-setting-end', value.end)
                        .attr('data-setting-slots', value.slots)
                        .doon();

                    // Adds the data for delete button
                    var delMessage = 'Are you sure you want to delete this date ('+value.date+')?';
                    detail.find('a.availability-delete')
                        .attr('data-id', value.id)
                        .attr('data-message', delMessage)
                        .doon();

                    detail.doon();

                    $('.interview-setting-list').append(detail);
                    detail.slideDown(500);
                });

                setTimeout(function() {
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                        $('#interview-availability-add-clone.in').modal('hide');
                    }

                    // Gets the list of dates to block
                    $.get('/ajax/interview/availability/list', function(response) {
                        response = JSON.parse(response);

                        // Checks if there were no errors
                        if (!response.error) {
                            var dates = response.results;

                            // Recreate the datepicker
                            $('.landing-datepicker').datepicker('destroy');
                            $('.landing-datepicker').datepicker({
                                beforeShowDay: function(date){
                                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                    return [ dates.indexOf(string) == -1 ]
                                }
                            });
                        }
                    });
                }, 2000);
            });

            return false;
        });

        // Edit the availability
        $(window).on('availability-edit-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).find('.btn.btn-default').attr('disabled', 'disabled');

            var data = $('#interview-availability-edit-clone.in').find('form').formToJson();
            var url = '/ajax/interview/availability/edit/'+data.interview_setting_id;

            // sends the data via ajax
            $.post(url, data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    toastr.error(response.message);

                    // Checks if errors were returned
                    if (typeof response.errors !== 'undefined') {
                        // Loops through the errors
                        $.each(response.errors, function(index, value) {
                            toastr.error(value);
                        });
                    }

                    // Allow submitting again
                    $(target).find('.btn.btn-default').removeAttr('disabled');
                    return;
                }

                // There are no errors at this point
                // Edit the existing element
                var detail = $('#interview-detail-'+data.interview_setting_id);

                detail.addClass('disabled');
                detail.find('i.fa-spinner').removeClass('hide');
                detail.find('button.dropdown-toggle').addClass('hide');

                toastr.info(
                    'Updating Availability <i class="fa fa-spinner fa-pulse"></i>',
                    'Please Wait',
                    { timeOut: 1500 }
                );

                // Wait for 2 seconds
                setTimeout(function() {
                    // Checks if there is a custom message
                    if (typeof response.message !== 'undefined') {
                        toastr.success(response.message);
                    }

                    detail.removeClass('disabled');
                    detail.find('i.fa-spinner').addClass('hide');
                    detail.find('button.dropdown-toggle').removeClass('hide');

                    detail.find('.setting-slots span').html(response.results.slots);
                    detail.find('.setting-date span').html(response.results.date);
                    detail.find('.setting-time span').html(response.results.start+' - '+response.results.end);

                    $('#interview-availability-edit-clone.in').modal('hide');

                    // Gets the list of dates to block
                    $.get('/ajax/interview/availability/list', function(response) {
                        response = JSON.parse(response);

                        // Checks if there were no errors
                        if (!response.error) {
                            var dates = response.results;

                            // Recreate the datepicker
                            $('.landing-datepicker').datepicker('destroy');
                            $('.landing-datepicker').datepicker({
                                beforeShowDay: function(date){
                                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                    return [ dates.indexOf(string) == -1 ]
                                }
                            });
                        }
                    });
                }, 2000);
            });

            return false;
        });

        $(window).on('availability-list-init', function(e, target) {
            if ($('.interview-setting-list .interview-setting-detail').length != 0) {
                $('.interview-setting-list').css('min-height', '120px');
            }
        });

        // Edits the contact detail
        $(window).on('contact-detail-update-click', function(e, target) {
            // Let's disable the button from submitting again
            $(target).attr('disabled', 'disabled');

            var data = $('.contact-edit').find('form').formToJson();
            var url = '/ajax/interview/contact/update';

            // sends the data via ajax
            $.post(url, data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    modalClose = false;
                    toastr.error(response.message);

                    // Checks if errors were returned
                    if (typeof response.errors !== 'undefined') {
                        // Loops through the errors
                        $.each(response.errors, function(index, value) {
                            toastr.error(value);
                        });
                    }

                    // Allow submitting again
                    $(target).removeAttr('disabled');
                    return;
                }

                toastr.info(
                    'Updating Contact Details <i class="fa fa-spinner fa-pulse"></i>',
                    'Please Wait',
                    { timeOut: 1500 }
                );

                // There are no errors at this point
                // Lets replace the contents
                $('.contact-display').find('.contact-person span')
                    .html(data.contact_person);
                $('.contact-display').find('.contact-number span')
                    .removeClass('placeholder-italic')
                    .html(data.contact_number);
                $('.contact-display').find('.contact-email span')
                    .html(data.contact_email);
                $('.contact-display').find('.contact-address span')
                    .removeClass('placeholder-italic')
                    .html(data.contact_address);

                // Close after editing
                setTimeout(function() {
                    toastr.success(response.message);

                    $('.contact-display').slideDown(500);
                    $('.contact-edit').slideUp(500);
                    $(target).removeAttr('disabled');
                }, 2000);
            });

            e.preventDefault();
        });

        // Shows the edit contact
        $(window).on('contact-detail-change-click', function(e, target) {
            $('.contact-display').slideUp(500);
            $('.contact-edit').slideDown(500);
        });

        // Hides the edit contact
        $(window).on('contact-detail-cancel-click', function(e, target) {
            $('.contact-display').slideDown(500);
            $('.contact-edit').slideUp(500);
        });

        // Shows the calendar list
        $(window).on('calendar-schedule-click', function(e, target) {
            /// Variable declaration
            var id = $(target).data('id');
            var elemId = '#schedule-list-' + id;

            // Loops through all the lists
            $('.schedule-list').each(function(index, value) {
                if ($(value).data('id') != id) {
                    if ($(value).css('display') != 'none') {
                        $(value).slideUp(500);
                        $(value).parent().find('.fa-angle-up').slideUp(500);
                        $(value).parent().find('.fa-angle-down').slideDown(1000);
                    }
                }
            });

            // Checks if the element is open
            if ($(elemId).css('display') == 'block') {
                // Open the selected list
                $(elemId).slideUp(500);
                $(target).find('.fa-angle-up').slideUp(500);
                $(target).find('.fa-angle-down').slideDown(1000);
            } else {
                // Closes the selected list
                $(elemId).slideDown(500);
                $(target).find('.fa-angle-down').slideUp(500);
                $(target).find('.fa-angle-up').slideDown(1000);
            }
        });

        // Submits the form if the date has been change
        $(window).on('calendar-datepicker-change', function(e, target) {
            $('form#calendar-datepicker').submit();
        });

        // Show the Datepicker
        $(window).on('choose-date-click', function(e, target) {
            $('.datepicker').datepicker('show');
        });

        // Shows the reschedule modal
        $(window).on('calendar-reschedule-click', function(e, target) {
            toastr.info(
                'Fetching Dates <i class="fa fa-spinner fa-pulse"></i>',
                'Please Wait',
                { timeOut: 2500 }
            );

            var clone = $('#interview-reschedule').clone();
            clone.attr('id', 'interview-reschedule-clone');
            clone.find('.btn-reschedule').attr('data-id', $(target).data('id'));

            // Creates the url
            var url = '/ajax/interview/setting/' + $(target).data('interview');

            // Gets the list of interviews via ajax
            $.get(url, function(response) {
                response = JSON.parse(response);

                // Loops through the results
                $.each(response.results, function(index, value) {
                    var option = $('.date-option.hide').clone();
                    option.removeClass('hide');

                    var elementId = 'setting-detail-' + value['interview_setting_id'];
                    option.find('a').attr('data-id',value['interview_setting_id']);
                    option.find('.setting-detail').attr('id', elementId);

                    option.find('input[name="slots_taken"]').val(value['slots_taken']);
                    option.find('input[name="max_slots"]').val(value['interview_setting_slots']);

                    var elementBody = '<i class="fa fa-user"></i> ' + value['slots_taken'].toLocaleString() + '/'
                        + value['interview_setting_slots'].toLocaleString();
                    option.find('.slots').html(elementBody);

                    option.find('.date').html(value['interview_setting_date_format']);

                    var elementBody = value['interview_setting_start_format'] + ' - '
                        + value['interview_setting_end_format'];
                    option.find('.setting-time span').html(elementBody);

                    // Appends the new option to the list
                    clone.find('.interview-setting-list').append(option);
                });

                var option = $('.date-option.hide').clone();
                    option.removeClass('hide');
                var addAvailabilityHtml = '<a href="/profile/interview/settings" style="text-align:center;">'
                    + '<i class="fa fa-plus"></i>'
                    + ' Add Availability</a>';
                    option.html(addAvailabilityHtml);

                clone.find('.interview-setting-list').append(option);

                clone.modal('show');
                clone.doon();
            });

            return false;
        });

        // Reschedules the interview
        $(window).on('reschedule-interview-click', function(e, target) {
            // Disables the button
            $(target).attr('disabled', 'disabled');

            var schedule = $(target).data('id');
            var data = $('#interview-reschedule-clone form').formToJson();
            data.interview_schedule_id = schedule;

            // Checks if there is no interview setting selected
            if (data.interview_setting_id == '') {
                // The form is incomplte
                // Sends an error message
                toastr.error('Please select a date to reschedule the interview');

                // Removes the disabled button
                $(target).removeAttr('disabled');
            }

            $(target).html('Rescheduling <i class="fa fa-spinner fa-pulse"></i>');

            // We can send the form now
            $.post('/ajax/interview/schedule/reschedule', data, function(response) {
                response = JSON.parse(response);

                // Checks for errors
                if (response.error) {
                    // Sends an error message
                    toastr.error(response.message);

                    // Change the button
                    $(target).html('Confirm');

                    // Removes the disabled button
                    $(target).removeAttr('disabled');
                }

                // There are no errors at this point
                toastr.success(response.message);

                // Reloads the webpage
                delayedRedirect('self', 2500);
            });

            return false;
        });

        // Modal close event
        $(window).on('hide.bs.modal', function (e, target) {
            // Destroy any add availability modals
            setTimeout(function() {
                // Checks if a new modal has been opened
                if ($('.modal.in').length !== 1) {
                    $('#interview-availability-add-clone').remove();
                    $('#interview-availability-edit-clone').remove();
                    $('#confirmation-interview-current').remove();
                    $('#interview-reschedule-clone').remove();
                }
            }, 500);
        });

        /**
         * Allow Interviews
         */
        $(window).on('interview-post-click', function(e, target) {
            // set the data
            var data = {
                'post_id': $(target).data('id'),
                'post_package' : $(target).data('package')
            };

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            toastr.info(
                'Updating your post <i class="fa fa-spinner fa-pulse"></i>',
                'Please Wait',
                { timeOut: 2500 }
            );

            // send post request
            $.post('/ajax/interview/post', data, function (response) {
                response = JSON.parse(response);

                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);

                    // Redirect to the add credit page
                    window.location.href = "/profile/credit/checkout";
                    return false;
                }

                // There are no errors at this point
                toastr.success(response.message);

                // Send a delayed reload
                delayedRedirect('self', 2500);

                return false;
            });
        });

        // Reusable function to populate and show ATS confirmation modal
        var interviewModalContent = function(e, data) {
            // Gets the modal
            var modal = $('#confirmation-interview').clone();
            modal.doon();
            modal.attr('id', 'confirmation-interview-current');

            // Populates the data
            modal.find('.modal-title').html(data.title);
            modal.find('.modal-message').html(data.message);
            modal.find('.btn.btn-default').html(data.action);
            modal.find('.modal-form').attr('data-type', data.type);

            // Checks if there is a subtitle
            if (typeof data.subtitle !== 'undefined') {
                modal.find('.sub-title')
                    .removeClass('hide')
                    .html(data.subtitle)
            }

            if (typeof data.tag !== 'undefined') {
                modal.find('.modal-form').attr('data-tag', data.tag);
            }

            if (typeof data.id !== 'undefined') {
                modal.find('.modal-form').attr('data-id', data.id);
            }

            // Shows the modal
            modal.modal('show');
            e.preventDefault();
        }
    })();

    /**
     * Background Move
     */
    $(window).on('background-move-init', function(e, target) {
        var movementStrength = 50,
            height = movementStrength / $(window).height(),
            width = movementStrength / $(window).width();
        $(target).mousemove(function(e) {
            var pageX = e.pageX - ($(window).width() / 2),
                pageY = e.pageY - ($(window).height() / 2),
                newvalueX = width * pageX * -1 - 25,
                newvalueY = height * pageY * -1 - 50;

            $(target).css('background-position', newvalueX+'px     '+newvalueY+'px');
        });
    });

    /**
     * FlagStrap
     */
    $(window).on('flag-strap-init', function(e, target) {
        $(target).flagStrap({
            countries: {
                "PH": "Philippines",
            },
            placeholder: false,
            selectedCountry: 'PH'
        });
    });

    /**
     * Expand More
     */
    $(window).on('expand-more-click', function(e, target) {
        $(".page-home .home-jobs-search ul li.hide").toggleClass("show");
        if ($('.page-home .home-jobs-search ul li').hasClass('hide show')) {
            $('.page-home .home-jobs-search .circle .fa').removeClass('fa-caret-down');
            $('.page-home .home-jobs-search .circle .fa').addClass('fa-caret-up');
            $('.page-home .home-jobs-search ul li a .see-more').text('');
        } else {
            $('.page-home .home-jobs-search .circle .fa').removeClass('fa-caret-up');
            $('.page-home .home-jobs-search .circle .fa').addClass('fa-caret-down');
            $('.page-home .home-jobs-search ul li a .see-more').text('See more');
        }
    });

    /**
     * Toogle Matches Search
     */
    $(window).on('match-search-click', function(e, target) {
        $('header .header-job-match').slideToggle(300);
    });

    /**
     * Confirmation Modal
     */
    $(window).on('confirm-modal-click', function(e, target) {
        var modal = $('#confirm-modal'),
            link = $(target).data('link');
            title = $(target).data('title');

        // set the title
        $('.modal-body p', modal).html(title);
        // append the link
        $('.modal-footer a', modal).attr('href', link);
        // show the modal
        modal.modal('show');
    });

    /**
     * Boost Modal
     */
    $(window).on('boost-modal-click', function(e, target) {
        var modal = $('#boost-modal'),
            action = $(target).data('action');
            button = $(target).data('boost');
            id = $(target).data('id');
            link = $(target).data('link');
            position = $(target).data('position');
            reload = $(target).data('reload')
            title = $(target).data('title');
            subtitle = $(target).data('subtitle');
            package = $(target).data('package');

        // set values
        $('.modal-body p', modal).html(title);
        $('.modal-body span', modal).html(subtitle);
        $('.modal-body div.position', modal).html(position);
        $('.modal-footer a.boost-button', modal).html(button);
        $('.modal-footer a', modal).attr('href', link);
        $('.modal-footer a', modal).attr('data-package', package);
        $('.modal-footer a', modal).attr('data-do', action);
        $('.modal-footer a', modal).attr('data-id', id);
        $('.modal-footer a', modal).attr('data-reload', reload);
        // show the modal
        modal.modal('show').doon();
    });

    /**
    * Post Detail Styling
    *
    **/
    $(window).on('detail-init', function(e, target) {
        if (window.location.href.indexOf('/profile/post/search?filter[post_active]=0') !== -1) {
            $('.page-profile-post-search .post-matches').css({'padding-left':'45px'});
            $('.page-profile-post-search .post-interested').css({'padding-left':'65px'});
            $('.page-profile-post-search .post-actions a').css({'padding-left':'55px'});
        }
        if (window.location.href.indexOf('/profile/post/search?post_expires=-1') !== -1 ) {
            $('.post-detail-title, .post-title').css({'width':'300px'});
            $('.post-detail-matches, .post-detail-interested, .post-matches, .post-interested').css({'width':'225px'});
            $('.page-profile-post-search .post-actions a').css({'margin':'2px'});
        }
    });

    /**
    * Match Detail Styling
    *
    **/
    $(window).on('match-detail-init', function(e, target) {
        if (window.location.href.indexOf('/profile/match/search?post_expires=-1') !== -1) {
             $('.page-profile-match-search .post-status a:eq(2)').addClass('active');
             $('.page-profile-match-search .post-status a:eq(0)').removeClass('active');
        }
        if (window.location.href.indexOf('/profile/match/search?filter[post_active]=0') !== -1) {
             $('.page-profile-match-search .post-status a:eq(1)').addClass('active');
             $('.page-profile-match-search .post-status a:eq(0)').removeClass('active');
        }
    });

    /**
     * Scroll To Top
     */
    $(window).on('scroll-to-top-click', function(e, target) {

        if(window.location.pathname != '/') {
            window.location = '/';
            return;
        }

        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

    $('#widget-subscribe-form').on("submit", function() {
        toastr.success('You have successfully subscribed to our newsletter');
        $('#coming_soon').modal('hide');
        delayedRedirect('self', 3500);
    });

    /**
    *   FAQ click collapsible icon
    *
    */
    $(window).on('show-collapse-click', function (e, target) {
        $('.collapse').on('shown.bs.collapse', function(){
        $(this).parent().find(".glyphicon-plus").removeClass("glyphicon-plus").addClass("glyphicon-minus");
        }).on('hidden.bs.collapse', function(){
        $(this).parent().find(".glyphicon-minus").removeClass("glyphicon-minus").addClass("glyphicon-plus");
        });
     });

    /**
    *   header buy credits tooltip
    *
    */
    $(window).on('tooltip-container-init', function (e, target) {
        $('.credits').hover(function(){
            $('.tooltip-credits').show();
        });

        $(window).click(function() {
            //Hide the menus if visible
            $('.tooltip-credits').hide();
        });

        // hide tooltip on click in .dropdown-toggle
        $('.dropdown-toggle').click(function(){
            $('.tooltip-credits').hide();
        });

        $('.tooltip-credits').click(function(event){
          event.stopPropagation();
        });

    });

    /**
    *   header mouseleave buy credits tooltip
    *
    */
    $(window).on('tooltip-credits-init', function (e, target) {
        $('.tooltip-credits').mouseleave(function(){
            $(this).hide();
        });
     });

    /**
     * Confirm password streght
     */
    $(window).on('password-strength-keyup', function(e, target) {
        var strength = 0;
        var pass_length = $("#pass-strength").val().length;
        var pass_val = $("#pass-strength").val();
        if (pass_length == 0) strength = 0
        if (pass_length > 0) strength += 1
        if (pass_length > 6) strength += 1
        // If password contains both lower and uppercase characters, increase strength value.
        if (pass_val.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
        // If it has numbers and characters, increase strength value.
        if (pass_val.match(/([a-zA-Z])/) && pass_val.match(/([0-9])/)) strength += 1
        // If it has two special character, increase strength value.
        if (pass_val.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
        // If it has four special characters, increase strength value.
        if (pass_val.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
        // Calculated strength value, we can return messages
        if (strength == 0) {
            $('#divider-1, #divider-2, #divider-3, #divider-4, #divider-5').css("border-color", "#8c8c8c");
        }
        if (strength == 1) {
             $('#divider-1').css("border-color", "#ff0000");
             $('#divider-2, #divider-3, #divider-4, #divider-5').css("border-color", "#8c8c8c");
        }
        if (strength == 2) {
            $('#divider-1, #divider-2').css("border-color", "#ff0000");
            $('#divider-3, #divider-4, #divider-5').css("border-color", "#8c8c8c");
        }
        if (strength == 3) {
            $('#divider-1, #divider-2, #divider-3').css("border-color", "#ff8000");
            $('#divider-4, #divider-5').css("border-color", "#8c8c8c");
        }
        if (strength == 4) {
            $('#divider-1, #divider-2, #divider-3, #divider-4').css("border-color", "#34ce34");
        }
        if (strength == 5) {
            $('#divider-1, #divider-2, #divider-3, #divider-4, #divider-5').css("border-color", "#34ce34");
        }
    })

    /**
    *   Hide fix div when overlap
    *
    */
    $(window).on('social-overlap-init', function(e, target) {
        $(window).scroll(function(){
            // top of .container-blog-social-share = 175
            // padding top of foot = 50
            // total = 225

            var height_difference = $('.body').height() + $('.head').height() - 230;
            if($(this).scrollTop() >= height_difference) {
                $('.container-blog-social-share').hide();
            }
            else {
                $('.container-blog-social-share').show();
            }
        });
    });

    /**
     * User Dashboard Dropdown
     */
    $(window).on('dashboard-dropdown-click', function(e, target) {
        $(target).parent().toggleClass('open');
    });

    /**
     * Lazy Load Image
     */
    $(window).on('image-lazy-init', function(e, target) {
        $(target).Lazy({
            effect: 'fadeIn',
            effectTime: 2000,
        });
    });

    /**
    * trigger file-upload-click when keypress enter
    */
    $(window).on('trigger-upload-init', function(e, target) {
        $(target).keypress(function(e) {
            if(e.keyCode == 13) {
                $(".modal-footer button").click();
                e.preventDefault();
            }
        })
    });

    /**
     * Company salary input: You can have multiple events on one data-on
     * source: https://github.com/cblanquera/doon
     */
    $(window).on('salary-input-init', function(e, target) {
        var target = $(target);
        value = $.trim(target.val());
        // format digits with comma
        target = target.val(value.replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }).on('salary-input-keydown', function(e, target) {
        //if the letter is not digit then display error and don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        }
    }).on('salary-input-keyup', function(e, target) {
        // format the numbers with comma
        var target  = $(target);
        value = $.trim(target.val());
        target.val(value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    });

    /**
    * prevent user letter input
    */
    $(window).on('prevent-letter-input-keypress', function(e, target) {
        //if the letter is not digit then display error and don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        }
    });

    /**
    * prevent user letter input
    */
    $(window).on('prevent-letter-input-init', function(e, target) {
        var target  = $(target);
        value = $.trim(target.val());
        target = target.val(value);
    });

    /**
    * Remove spaces on page load
    */
    $(window).on('remove-spaces-init', function(e, target) {
        var target  = $(target);
            value = $.trim(target.val());
            target = target.val(value);
    });

    /**
    * Prevent spaces
    */
    $(window).on('remove-spaces-keypress', function(e, target) {
        $('#this').bind('input', function(){
            $(this).val(function(_, e){
                return e.replace(/\s+/g, '');
            });
        });
    });

    /**
    * Message job seeker modal
    */
    $(window).on('message-jobseeker-modal-click', function(e, target) {
        var target = $(target);
            profileId = target.attr('data-profile-id');
            $('.page-post-detail .modal .modal-footer .btn.btn-primary[data-profile-id]').css('pointer-events', 'unset');
            modal = $('.message-jobseeker-modal[data-profile-id="' + profileId + '"]');
        modal.modal('show');
    });

    /**
    * Message job seeker
    */
    $(window).on('message-jobseeker-click', function(e, target) {
        $('#conpany-form').submit();
    });

    /**
    * Send email message modal
    */
    $(window).on('message-modal-click', function(e, target) {
        var modal = $('#message-modal'),
            id = $(target).data('profile-id');
            name = $(target).data('name');
            type = $(target).data('type');
            img = $(target).data('img');
            imgUrl = "this.src='/images/avatar/avatar-"+(id % 5)+".png'";

        // set values
        modal.attr('data-profile-id', id);

        if (type == "poster") {
            $('.modal-header .modal-title', modal).html('message this job poster');
            $('.message-modal input[name="post_type"]').val('seeker');
        }else{
            $('.modal-header .modal-title', modal).html('message this job seeker');
            $('.message-modal input[name="post_type"]').val('poster');
        }
        $('.modal-header .modal-image span', modal).html(name);
        $('.modal-header .modal-image-circle img', modal).attr('src', img);
        $('.modal-header .modal-image-circle img', modal).attr('onerror', imgUrl);
        $('.modal-content #conpany-form', modal).attr('data-profile-id', id);
        $('.modal-content .modal-body textarea', modal).attr('data-profile-id', id);
        $('.modal-footer div button.send-email', modal).attr('data-profile-id', id);

        // show the modal
        modal.modal('show').doon();
    });

    /**
    *   Textarea data upload
    */
    $(window).on('textarea-data-upload-submit', function(e, target) {
        var target = $(target);
            profile_id = target.attr('data-profile-id');
            post_detail = $('textarea[data-profile-id="' + profile_id + '"]').val();
            post_slug = $('input[name="post_slug"]').val();
            post_type = $('#conpany-form .form-group input[name="post_type"]').val();
            data = {
                profile_id: profile_id,
                post_detail: post_detail,
                post_slug: post_slug,
                post_type: post_type,
            };
        // disable button
        $(target).find('button').attr("disabled", "disabled").css({"cursor": "wait"});

        // If textarea is empty return error message
        if (data['post_detail'] == "") {
            toastr.error('Message is empty!');
            return false;
        };

        if ($('.page-post-message-seeker').length == 1) {
            $('.page-post-message-seeker .message-jobseeker-footer .btn.btn-default')
                .attr("disabled", "disabled").css({"cursor": "wait"});
            toastr.info('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i> Sending message...');
        };

        $.post('/ajax/message/jobseeker' , data, function(response) {
            if(response.error) {
                toastr.error(response.message);
                return;
            }

            $('.page-post-detail .modal .modal-footer .btn.btn-primary')
                .css('pointer-events', 'none');

            toastr.success('Message has been sent!');

            // if (data.post_type == "seeker") {
            //     toastr.success('Message has been sent to job seeker!');
            // } else{
            //     toastr.success('Message has been sent to job poster!');
            // }
        });

        e.preventDefault();
        setTimeout(function() {
            $('.modal-footer button.close').click();
            if ($('.page-post-message-seeker').length == 1) {
                $('.page-post-message-seeker .message-jobseeker-footer .btn.btn-default').css('pointer-events', 'unset');
            };
            location.reload();
        }, 7000);
    });

    /**
     * Read more & Read less
     */
    $(window).on('read-more-click', function(e, target) {
        if ($('.read-more').hasClass('less') == false) {
            $('.read-more').text('');
            $('.read-more').addClass('less');
            $('.post-arrow .fa').removeClass('fa-caret-down');
            $('.post-arrow .fa').addClass('fa-caret-up');
        } else {
            $('.read-more').text('read more');
            $('.read-more').removeClass('less');
            $('.post-arrow .fa').removeClass('fa-caret-up');
            $('.post-arrow .fa').addClass('fa-caret-down');
        }
    });

    /**
     *   Profile name tooltip
     */
    $(window).on('profile-name-tooltip-init', function (e, target) {
        $('header .profile-name-link').hover(function(){
            $('.level-rank-tooltip').show();
            $('.tooltip-credits').hide();
        });

        $('header .profile-name-link').mouseleave(function(){
            $('.level-rank-tooltip').hide();
        });

    });

    /**
     * Toggle Sidebar
     */
    $(window).on('sidebar-panel-click', function (e, target) {
        if($(target).hasClass('open')) {
            $('.search-panel').animate({
                left: '+=250'
            }, 300);

            $(target)
                .html('<i class="fa fa-angle-double-left" aria-hidden="true"></i>')
                .removeClass('open')
                .addClass('close');
        } else {
            $('.search-panel').animate({
                left: "-=250"
            }, 300);

            $(target)
                .html('<i class="fa fa-angle-double-right" aria-hidden="true"></i>')
                .removeClass('close')
                .addClass('open');
        }
    });

    /**
     * Auto Submit Form
     */
    $(window).on('sidebar-form-click', function (e, target) {
        // check if filter all
        if ($(target).attr('id') == 'type_all' || $(target).attr('id') == 'type-all-panel') {
            $(target).closest('form').attr('action', '/post/search');
            $(".radio").prop( "checked", false );
            $(target).prop( "checked", true );
        }

        // check for post type if seeker
        if ($(target).attr('id') == 'type_seeker' || $(target).attr('id') == 'type-seeker-panel') {
            $(target).closest('form').attr('action', '/Job-Seekers-Search');
           $(".radio").prop( "checked", false );
            $(target).prop( "checked", true );
        }

        // check for post type if poster
        if ($(target).attr('id') == 'type_poster' || $(target).attr('id') == 'type-poster-panel') {
            $(target).closest('form').attr('action', '/Job-Search-Companies');
            $(".radio").prop( "checked", false );
            $(target).prop( "checked", true );
        }

        // submit the form
        // $(target).closest('form').submit();
    });

    /**
     * Show/Hide Sidebar Panel
     */
    $(window).on('panel-scroll-init', function (e, target) {
        if ($(window).width() < 980) {
            $(window).on('scroll', function() {
                var scrollHeight = $(document).height(),
                    footer = $('footer').height();

                if ($(document).scrollTop() >= (scrollHeight - footer - 120)) {
                    $('.search-panel').hide();
                } else {
                    $('.search-panel').show();
                }
            });
        }
    });

    // Reusable function to populate and show ATS Form with Questions
    var previewForm = function(button, form, questions, id) {
        var id = id || "sendResume";
        var clone = $('#sendResume').clone();

        clone.doon();
        clone.attr('id', id)
            .find('.form-questions')
            .removeClass('hide');

        // Loops through the questions
        $.each(questions, function(index, question) {
            // Clones the question form
            var questionElement = clone.find('.form-questions').find('.form-question.hide').clone();

            // Show the element
            questionElement.removeClass('hide');

            // Append the question_name
            questionElement.find('.question-name').html(question.question_name);

            // Loops through the question_choices
            $.each(question.question_choices, function(i, choice) {
                // Clone the answer element
                var questionAnswer = questionElement.find('.question-answer.hide').clone();

                // add form _id
                clone.find('.form-questions #form_id').val(form.form_id);

                // Show the element
                questionAnswer.removeClass('hide');

                // Varaible declaration for replacing
                var answerName = 'question[' + question.question_id + ']';
                questionAnswer.find('input').attr('name', answerName).val(choice);
                questionAnswer.find('input').attr('id', question.question_id+'-question-'+i);
                questionAnswer.find('label').html(choice);
                questionAnswer.find('label').attr('for', question.question_id+'-question-'+i);
                questionElement.find('.question-answers').append(questionAnswer);
            });

            // Checks if custom answers are allowed
            if ($.inArray('custom', question.question_type) !== -1) {
                // Varaible declaration for replacing
                var answerName = 'answer_custom[' + index + ']';

                // Show the input field / custom_answer
                questionElement.find('.question-answers .custom-answer')
                    .removeClass('hide')
                    .attr('name', answerName)
                    .attr('placeholder', 'Custom Answer');
            }

            // Checks if custom answers are allowed
            if ($.inArray('file', question.question_type) !== -1) {
                // Varaible declaration for replacing
                var answerName = 'answer_file[' + index + ']';

                // Show the input field / custom_answer
                questionElement.find('.question-answers .request-file')
                    .removeClass('hide')
                    .attr('name', answerName);
            }

            // Apend the entire question
            clone.find('.form-questions').append(questionElement);
        });

        // Checks if the modal for for form preview
        if (id == 'form-preview') {
            clone.find('.modal-title').html(form.form_name);
            clone.find('.resume-container-title').hide();
            clone.find('.choose-resume-group').hide();
            clone.find('.upload-group').hide();
            clone.find('#no-thanks')
                .replaceWith('<button class="btn btn-primary" data-dismiss="modal">Cancel</button>');
        }

        setTimeout(function() {
            clone.modal('show');
            button.css('pointer-events','all');
        }, 2500);
    };

    // Redirect with a delay
    var delayedRedirect = function (url, time) {
        var time = time || 1000;
        if (url == 'self') {
            url = window.location;
        }

        setTimeout(function() {
            // Redirect to the form update page
            window.location = url;
        }, time);
    }

    /**
     * Attach Label
     */
    var attachLabel = function (data, target) {
        var post = [];
        //determine if bulk or not
        if (data.applicant_ids) {
            post.push({'applicant_ids': data.applicant_ids});
        } else {
            post.push({'applicant_id': data.applicant_id});
        }

        post.push({'label_name': data.label_name});

        //attach label
        $.ajax({
            url: '/ajax/applicant/attach/label',
            type: 'POST',
            async: false,
            data: data,
            success: function (response) {
                response = JSON.parse(response);

                if (response.error) {
                    toastr.error(response.message);
                } else {
                    toastr.success(response.message);
                    $.each(response.results, function(index, applicant) {
                        // Checks if a status was returned
                        if (applicant.applicant_status) {
                            var elementId = '#applicant-detail-list-'+applicant.applicant_id;
                            var labelTemplate = $('.label-default.hide').clone();
                            labelTemplate.attr('data-label', data['label_name']);
                            var className = 'applicant-remove-label-'+applicant.applicant_id;

                            labelTemplate.removeClass('hide')
                                .find('i').html(data['label_name']);

                            labelTemplate.find('a')
                                .attr('data-label-name', data['label_name'])
                                .attr('data-applicant-id', applicant.applicant_id)
                                .attr('class', className)
                                .doon();

                            $(elementId).find('.list-label').append(labelTemplate);
                        }
                    });
                }
            }
        });

        return;
    };

    // Delete the question from the form
    var deleteElement = function(element, id, find) {
        var find = find || "";

        if ($.isArray(id)) {
            $.each(id, function() {
                var elementName = '#'+element+'-'+this

                if (find != '') {
                    var Element = $(elementName).find(find);
                } else {
                    var Element = $(elementName);
                }

                Element.hide(500);

                setTimeout(function() {
                    Element.remove();
                }, 2000);
            })

            return false;
        }

        var elementName = '#'+element+'-'+id

        if (find != '') {
            var Element = $(elementName).find(find);
        } else {
            var Element = $(elementName);
        }

        Element.hide(500);

        setTimeout(function() {
            Element.remove();
        }, 2000);

        return false;
    }

    var validateApplicationForm = function () {
        var questions = $('.question-answer:visible input.choice-answer');
        var custom = $('.question-answers:visible input.custom-answer-only:visible');
        var file = $('.question-answers:visible input.request-file-hidden[name^=question]');
        var fileOversized = false;
        var answered = 0;
        var answer = [];

        questions.each(function() {
            if(answer.indexOf($(this).attr('name')) == -1) {
                answer.push($(this).attr('name'));
            }

            if ($(this, 'name["'+ $(this).attr('name') +'"]').is(':checked')) {
                answered++;
            }
        });

        custom.each(function() {
            if ($(this).val() != '') {
                answered++;
            }
        });

        file.each(function() {
            if ($(this).val() != '') {
                answered++;
            }

            if ($(this).attr('size') > 2000000) {
                fileOversized = true;
            }
        });

        totalQuestion = answer.length  + file.length + custom.length;

        // check if there's a file oversized
        if (fileOversized) {
            toastr.error('File must be less than 2mb');
            return false;
        }

        // check answers form
        if (answered !== totalQuestion) {
            toastr.error('Fill up the form');
            return false;
        }

        return true;
    }

    /**
     *
     *  Custom File Upload
     *
     */
    $(window).on('custom-upload-init', function(evt, target) {
        $(target).on('fileselect', function(evt, numFiles, label) {
            var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

        }).on('change', function(evt, target) {
            var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

            input.trigger('fileselect', [numFiles, label]);

            // File Reader
            if (this.files && this.files[0]) {
                $(input).next('input').attr('size', this.files[0].size);

                // check for image size
                if (this.files[0].size > '2000000') {
                    toastr.error('File must be less than 2mb');

                    $(input).next('input').val('');

                    return;
                }

                var FR = new FileReader();
                FR.onload = function(e, target) {
                    $(input).next('input').val(e.target.result);
                };
                FR.readAsDataURL(this.files[0]);
            }
        });
    });


    /**
     *
     *  Custom Answer
     *
     */
    $(window).on('custom-answer-init', function(evt, target) {
        $(target).on('keyup', function(evt, target) {
            if ($(this).val() != '') {
            $('.question-answer input[name="'+$(this).attr('name')+'"].choice-answer.radio').prop('checked', false);
                $('.question-answer input[name="'+$(this).attr('name')+'"].radio').attr('disabled', 'disabled');
                $('#customAnswer[name="'+$(this).attr('name')+'"').prop('checked', true);
            } else {
                $('.question-answer input[name="'+$(this).attr('name')+'"].radio').removeAttr('disabled');
            }
        });
    });

    /**
    *
    *   Remove disabled on ATS
    *
    **/
    $(window).on('select-label-init', function (e, target) {
        $('.remove .btn-tag .dropdown-toggle').removeClass('disabled');
    });
    /**
     *
     *  Prevent enter
     *
     */
    $(document).keypress(function(e) {
        if ($("#confirmation-form-current").hasClass('in') && (e.keycode == 13 || e.which == 13)) {
            return false;
        }
    });

    /**
     *   Profile name tooltip
     */
    $(document).ready(function(){
        $(".airplane-image").hover(function(){
            var profile_id = $(this).attr('data-profile-id');
                $('.airplane-tooltip[data-profile-id="' + profile_id + '"]').show();
            }, function(){
                var profile_id = $(this).attr('data-profile-id');
                $('.airplane-tooltip[data-profile-id="' + profile_id + '"]').hide();
        });
    });

    /**
    *
    *   Payment Method
    *
    **/
    $(window).on('payment-method-click', function (e, target) {
        var $target = $(target);
        $($target).find('.payment-button').toggleClass('active');
    });

    /**
    *
    *  Set map height
    *
    **/
    $(window).on('feature-content-init', function (e, target) {
        $(window).on('load', function(){
            var featureContent = $('.feature-content').height();
            $('.map-style').height(featureContent);
        });
    });

    // Show the credit notification modal
    $(window).on('credits-alert-init', function(e, target) {
        if (!$('.achievement-modal').length) {
            $('#credit-notify-modal').modal('show');
        }
    });

    // Rigger test modal
    $('.achievement-modal').on('hidden.bs.modal', function () {
        // show the next modal
        $('#credit-notify-modal').modal('show');
    });

   /*
    *
    * Job location Scroll function
    *
    **/
    (function(){
        $(window).on('featured-location-init', function(e,target) {
            if (window.location.href.indexOf('/Job-Locations') !== -1) {
                $('header.head').remove();
                $('header.featured-header').removeClass('hide');
                $('.page-post-featured .wrapper').css({"margin-top": "167px"});
                if ($(window).width() <= 1171) {
                    $('.page-post-featured .wrapper').css({"margin-top": "0px"});
                }
            }
        });
    })();

    /**
     *   Profile Image tooltip
     */
    $(window).on('profile-image-tooltip-init', function (e, target) {
        $('.profile-image-div').hover(function(){
            $('.profile-image-tooltip').show();
        });
        $('.profile-image-div').mouseleave(function(){
            $('.profile-image-tooltip').hide();
        });
    });

    /**
     * school modal submit
     */
    $(window).on('school-modal-submit', function(e, target) {
        var $target = $(target),
            container = $target.data('container'),
            data = $target.serializeObject(),
            url = $target.attr('action');

        // disable the button
        $(target).find('button[type="submit"]').css({
            "cursor": "wait",
            "pointer-events": "none"
        });

        // reset the class info
        $target.find('.form-group')
            .removeClass('has-error')
            .find('.help-block')
            .html('');

        // send the data
        $.post(url, data, function (response) {
            // if there are errors
            if (response.error) {
                if (!response.validation) {
                    toastr.error(response.message);

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    return false;
                }

                toastr.error(response.message);

                $.each(response.validation, function(key, message) {
                    var element = $('input[name="'+key+'"], select[name="'+key+'"]', $target);

                    element.parents('.form-group').addClass('has-error');
                    element.parents('.form-group').find('.help-text').html(message);
                });

                // enable the button
                $(target).find('button[type="submit"]').css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                return false;
            }

            if (response.results) {
                // show message
                toastr.success('Post was updated');
                // process success
                setTimeout(function () {
                    if(!$('#post-'+$(target).data('id')+' .post-tips .tips-body > div').length) {
                        $('#post-'+$(target).data('id')+' .post-tips').remove();
                    }
                    window.location.reload();
                }, 2000);
            }
            setTimeout(function () {
              $("button.close").click();
            }, 2000);
        }, 'json');


        return false;
    });

    /**
    *   Question Mouseup Move
    *
    **/
    $(window).on('question-sort-init', function(e,target){
        $(target).mousedown(function(){
            $('.sortable-save').removeClass('hide');
        });
    });

    /**
     * Copy to Clipboard
     */
    $(window).on('copy-clipboard-click', function(e, target) {
        var target = $(target);
        var field = target.attr('data-target');
        var temp = $("<input>");

        // include in body html
        $("body").append(temp);

        // select the element
        temp.val($('#'+field).text()).select();

        document.execCommand("copy");
        // remove the template
        temp.remove();

        toastr.success('Copied');
    });

    /**
     * Copy Widget Code
     */
    $(window).on('copy-widget-code-click', function (e, target) {
        // get the target
        var target = $(target);
        // get the source
        var source = target.attr('data-target');

        // get the source
        source = document.getElementById(source);
        // select the text
        source.select();
        // copy text
        document.execCommand('copy', true);

        toastr.success('Source Code Copied!');
    });

    /**
    * Sortable function
    *
    **/
    $(function(){

        $(".question-sortable").sortable({
            stop: setPriority
        });
        setPriority();

        function setPriority() {
            $(".quesition-item").each( function(i){
                $(this).attr("data-priority", "pri-" + (i + 1));
            });

            $(".question_priority").each( function(i){
                $(this).val((i + 1));
            });
        }

    });

    /**
    *   Save data priority
    *
    **/
    $(window).on('save-priority-click', function(e, target) {
        // Let's disable the button from submitting again
        $(target).find('.btn.btn-default').attr('disabled', 'disabled');

        // Variable Declarations
        var jsonObj = [];
        $(".quesition-item").each( function(i){
            if ($(this).data('id')) {
                var id = $(this).data('id');
                var priority = $(this).find('.question_priority').val();

                item = {}
                item["question_id"] = id;
                item["question_priority"] = priority;
                // push every item to json variable
                jsonObj.push(item);
            }
        });

        var data = {'rows' : jsonObj};

        // Sends the data to be updated
        $.post('/ajax/question/priority', data, function(response) {
            response = JSON.parse(response);

            // Checks for errors
            if (response.error) {
                toastr.error(response.message);

                // Allow submitting again
                $(target).find('.btn.btn-default').removeAttr('disabled');
                return;
            }

            // There are no errors at this point
            // Checks if there is a custom message
            if (typeof response.message !== 'undefined') {
                toastr.success(response.message);
            } else {
                // At this point there is no custom message
                toastr.success('Save Changes');
                setTimeout(function () {
                    return window.location.reload();
                }, 1000);
            }

            // Allow submitting again
            $(target).find('.btn.btn-default').removeAttr('disabled');
        });

        e.preventDefault();
    });

    /**
     * Information
     */
    (function() {
        /**
         *
         * Form Information Create
         *
         */
        $(window).on('information-create-click', function(e, target) {
            var $target = $(target),
                modal = $target.data('modal'),
                title = $target.data('title');

            // show the modal
            $(modal).modal('show');
            // change the title
            $(modal).find('.modal-title').html(title);
            // clear the input value
            $(modal).find('input[type="text"]').val('');
            // clear the textarea value
            $(modal).find('textarea').val('');
            // select the first option value
            $(modal).find('select').val($(modal).find('select option:first').val());
        });

        /**
         *
         * Form Information Edit
         *
         */
        $(window).on('information-edit-click', function(e, target) {
            var $target = $(target),
                modal = $target.data('modal'),
                url = $target.data('detail'),
                form = $target.data('update'),
                title = $target.data('title');

            // send the data
            $.get(url,  function (response) {
                // check if error
                if (response.error) {
                    toastr.error(response.message);

                    return false;
                }

                // change the action of the form to update
                $(modal).find('form').attr('action', form);
                $(modal).find('.modal-title').html(title);

                // show the modal
                $(modal).modal('show');

                // loop the results
                $.each(response.results, function(key, value) {
                    // populate the data
                    $('input[name="'+key+'"], select[name="'+key+'"], textarea[name="'+key+'"]').val(value);
                });
            }, 'json');

            return false;
        });

        /**
         *
         * Form Information Confirm
         *
         */
        $(window).on('information-confirm-click', function(e, target) {
            var title = $(target).data('title'),
                action = $(target).data('action'),
                modal = $(target).data('modal');

            // show the confirmation modal
            $(modal).modal('show');
            // change the title
            $(modal).find('.modal-title').html(title);
            // change the for action
            $(modal).find('a').attr('data-action', action);
        });

        /**
         *
         * Form Information Remove
         *
         */
        $(window).on('information-remove-click', function(e, target) {
            var url = $(target).data('action');

            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // add button spinner
            $(target)
                .append('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');

            // send the data
            $.get(url, function (response) {
                // if there are errors
                if (response.error) {
                    toastr.error(response.message);

                    // enable the button
                    $(target).css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    // remove button spinner
                    $(target)
                        .find('.fa-spinner')
                        .remove();

                    return false;
                }

                // close the modal
                $(target).closest('.modal').modal('hide');

                setTimeout(function() {
                    $('#wrapper').html(response.results).doon()
                }, 1000);

                toastr.success('Information successfully updated');
            }, 'json');
        });

        /**
         *
         * Form Information
         *
         */
        $(window).on('information-form-submit', function(e, target) {
            var $target = $(target),
                container = $target.data('container'),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // reset the class info
            $target.find('.form-group')
                .removeClass('has-error')
                .find('.help-text')
                .html('');

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // add button spinner
            $(target)
                .find('button[type="submit"]')
                .append('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    if (!response.validation) {
                        toastr.error(response.message);

                        // enable the button
                        $(target).find('button[type="submit"]').css({
                            "cursor": "pointer",
                            "pointer-events": "auto"
                        });

                        // remove button spinner
                        $(target)
                            .find('button[type="submit"] .fa-spinner')
                            .remove();

                        return false;
                    }

                    toastr.error(response.message);

                    $.each(response.validation, function(key, message) {
                        var element = $('input[name="'+key+'"], select[name="'+key+'"]', $target);

                        element.parents('.form-group').addClass('has-error');
                        element.parents('.form-group').find('.help-text').html(message);
                    });

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    // remove button spinner
                    $(target)
                        .find('button[type="submit"] .fa-spinner')
                        .remove();

                    return false;
                }

                // close the modal
                $(target).closest('.modal').modal('hide');

                setTimeout(function() {
                    // check if url is profile/resume/search
                    if (window.location.pathname === '/profile/resume/search') {
                        return window.location.reload();
                    }

                    // append results
                    $('#wrapper').html(response.results).doon()
                }, 1000);

                // success message
                toastr.success('Information successfully updated');
            }, 'json');

            return false;
        });

        /**
         *
         * Form Information
         *
         */
        $(window).on('information-quick-submit', function(e, target) {
            var $target = $(target),
                container = $target.data('container'),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // reset the class info
            $target.find('.form-group')
                .removeClass('has-error')
                .find('.help-text')
                .html('');

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // add button spinner
            $(target)
                .find('button[type="submit"]')
                .append('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    if (!response.validation) {
                        toastr.error(response.message);

                        // enable the button
                        $(target).find('button[type="submit"]').css({
                            "cursor": "pointer",
                            "pointer-events": "auto"
                        });

                        // remove button spinner
                        $(target)
                            .find('button[type="submit"] .fa-spinner')
                            .remove();

                        return false;
                    }

                    toastr.error(response.message);

                    $.each(response.validation, function(key, message) {
                        var element = $('input[name="'+key+'"], select[name="'+key+'"]', $target);

                        element.parents('.form-group').addClass('has-error');
                        element.parents('.form-group').find('.help-text').html(message);
                    });

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    // remove button spinner
                    $(target)
                        .find('button[type="submit"] .fa-spinner')
                        .remove();

                    return false;
                }

                // close the modal
                $(target).closest('.modal').modal('hide');

                setTimeout(function() {
                    $('header .profile-name-link').html(response.results.profile_name);
                    $('.profile-info .profile-name').html(response.results.profile_name);
                    $('.profile-info .profile-job-detail').html(response.results.information_heading);
                }, 1000);

                // enable the button
                $(target).find('button[type="submit"]').css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                toastr.success('Information successfully updated');
            }, 'json');

            return false;
        });

        /**
         *
         *  Information City Search
         *
         */
        $(window).on('information-province-change', function(e, target) {
            // set the target
            var target = $(target),
                city = $('select[name="'+target.data('city')+'"]');

            // reset the dropdown
            city.html(city.find('option').first()).change();

            // update city
            if (!$(target).val()) {
                return false;
            }

            //set data
            var data = {
                filter: {
                    area_type: 'city',
                    area_parent: $(target).find('option:selected').data('id')
                },
                range: 0,
                nocache: 1
            };

            function ajaxCall() {
                $.get('/ajax/area/search', data, function(response) {
                    if (response.error) {
                        setTimeout(ajaxCall, 3000);

                        return;
                    }

                    // populate the data to option
                    $.each(response.results, function(key, val) {
                        city.append($('<option>')
                            .attr('value', val.area_name)
                            .data('id', val.area_id)
                            .html(val.area_name));
                    });
                }, 'json');
            }

            ajaxCall();
        });

        /**
         *
         *  Information Resume Upload
         *
         */
        $(window).on('information-resume-init', function(e, target) {
            var container = $(target),
                action = $(target).attr('action');

            container.on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            container.on('dragover', function (e) {
                container.find('.choose').css({'border-width':'2px'});

                e.stopPropagation();
                e.preventDefault();
            });

            container.on('dragleave', function (e) {
                container.find('.choose').css({'border-width':'1px'});

                e.stopPropagation();
                e.preventDefault();
            });

            container.on('drop', function (e) {
                e.preventDefault();

                // check for files
                if (e.originalEvent.dataTransfer.files.length > 1) {
                    toastr.error('Please upload 1 file only');

                    return;
                }

                // upload the resume
                informationUpload(e.originalEvent.dataTransfer.files);
            });

            // when try to browse
            $(container)
                .find('input[type="file"]')
                .change(function() {
                    // upload resume
                    informationUpload($(this).prop('files'));
                });

            var informationUpload = function(files) {
                var formData = new FormData();

                if (files) {
                    $.each(files, function(i, file) {
                        formData.append($(container).find('input[type="file"]').attr('name'), file);
                    });
                }

                $.ajax({
                    url: action,
                    type: 'post',
                    contentType: false,
                    processData: false,
                    cache: false,
                    data: formData,
                    beforeSend: function() {
                        toastr.info('Uploading resume');
                    },
                    success: function (response) {
                        // check for error
                        if (response.error) {
                            toastr.error(response.message);

                            return;
                        }

                        if (response.results) {
                            // append the results
                            $('#wrapper').html(response.results).doon()

                            // show modal
                            setTimeout(function() {
                                $('#information-view-modal').modal('show');
                            }, 1000);

                            // success message
                            toastr.success('Information successfully updated');

                            return;
                        }

                        // add error message
                        toastr.error('Something went wrong');
                    }
                });

                return false;
            };

            //prevent page from reacting on drop and drop
            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
        });

        /**
         *
         *  Information Create Modal
         *
         */
        $(window).on('information-create-modal-click', function(e, target) {
            var modal = '#information-create-modal',
                link = $(target).data('file-link');

            $(modal).find('input').val(link);

            $(modal).modal('show');
        });

        /**
         *
         *  Information Create Submit
         *
         */
        $(window).on('information-create-submit', function(e, target) {
            var $target = $(target),
                data = $target.serializeObject(),
                url = $target.attr('action');

            // info message
            toastr.info('Creating profile');

            // disable the button
            $(target).find('button[type="submit"]').css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // add button spinner
            $(target)
                .find('button[type="submit"]')
                .append('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');

            // send the data
            $.post(url, data, function (response) {
                // if there are errors
                if (response.error) {
                    toastr.error(response.message);

                    // enable the button
                    $(target).find('button[type="submit"]').css({
                        "cursor": "pointer",
                        "pointer-events": "auto"
                    });

                    // remove button spinner
                    $(target)
                        .find('button[type="submit"] .fa-spinner')
                        .remove();

                    // hide the current modal
                    $(target).closest('.modal').modal('hide');
                    // get the id of currenct modal
                    modalId = $(target).closest('.modal').attr('id')
                    // get the next modal to show
                    modal = $(target).data('modal');

                    // on hide
                    $('#'+modalId).on('hidden.bs.modal', function () {
                        // show the next modal
                        $('#information-modal').modal('show');
                    });

                    return false;
                }

                // close the modal
                $(target).closest('.modal').modal('hide');

                // enable the button
                $(target).find('button[type="submit"]').css({
                    "cursor": "pointer",
                    "pointer-events": "auto"
                });

                // remove button spinner
                $(target)
                    .find('button[type="submit"] .fa-spinner')
                    .remove();

                // success message
                toastr.success('Information successfully updated');

                // reload the page
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }, 'json');

            return false;
        });

        /**
         *
         *  Resume Upload
         *
         */
        $(window).on('resume-upload-init', function (e, target) {
            var container = $(target),
                action = $(target).attr('action');

            container.on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            container.on('dragover', function (e) {
                container.find('.choose').css({ 'border-width': '2px' });

                e.stopPropagation();
                e.preventDefault();
            });

            container.on('dragleave', function (e) {
                container.find('.choose').css({ 'border-width': '1px' });

                e.stopPropagation();
                e.preventDefault();
            });

            container.on('drop', function (e) {
                e.preventDefault();

                // check for files
                if (e.originalEvent.dataTransfer.files.length > 1) {
                    toastr.error('Please upload 1 file only');

                    return;
                }

                // upload the resume
                resumeUpload(e.originalEvent.dataTransfer.files);
            });

            // when try to browse
            $(container)
                .find('input[type="file"]')
                .change(function () {
                    // upload resume
                    resumeUpload($(this).prop('files'));
                });

            var resumeUpload = function (files) {
                var formData = new FormData();

                if (files) {
                    $.each(files, function (i, file) {
                        formData.append($(container).find('input[type="file"]').attr('name'), file);
                    });
                }

                $.ajax({
                    url: action,
                    type: 'post',
                    contentType: false,
                    processData: false,
                    cache: false,
                    data: formData,
                    beforeSend: function () {
                        toastr.info('Uploading resume');
                    },
                    success: function (response) {
                        // check for error
                        if (response.error) {
                            toastr.error(response.message);

                            return;
                        }

                        if (response.results) {
                            // // append the results
                            // $('#wrapper').html(response.results).doon()

                            // // show modal
                            // setTimeout(function () {
                            //     $('#information-view-modal').modal('show');
                            // }, 1000);

                            // success message
                            toastr.success('Resume successfully uploaded');

                            // reload the page
                            setTimeout(function(){
                                window.location.reload();
                            }, 1000);

                            return;
                        }

                        // add error message
                        toastr.error('Something went wrong');
                    }
                });

                return false;
            };

            //prevent page from reacting on drop and drop
            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });
        });

         /**
         * Enable Tracer Study
         */
        $(window).on('enable-tracer-click', function(e, target) {
            // disable the button
            $(target).css({
                "cursor": "wait",
                "pointer-events": "none"
            });

            // send post request
            $.get('/ajax/enable/tracer', function (response) {
                response = JSON.parse(response);

                // check for error
                if (response.error) {
                    // show message
                    toastr.error(response.message);

                    // Redirect to the add credit page
                    delayedRedirect('/profile/credit/checkout', 2500);
                    return false;
                }

                // There are no errors at this point
                toastr.success(response.message);

                // Send a delayed reload
                delayedRedirect('self', 2500);

                return false;
            });
        });
    })();

    /**
     *
     * Date Picker
     *
     */
    $(window).on('datepicker-init', function(e, target) {
        $(target).datepicker({
            inline: true,
            showOtherMonths: true,
            changeMonth: true,
            changeYear: true,
            dayNamesMin: ['S', 'M', 'T', 'W', 'Th', 'F', 'S'],
            dateFormat: 'MM dd, yy',
            yearRange: "-100:+0",
        });
    });

    /**
    *
    * Trigger Modal
    *
    */
    $(window).on('modal-trigger-click', function(e, target) {
        // hide the current modal
        $(target).closest('.modal').modal('hide');
        // get the id of currenct modal
        modalId = $(target).closest('.modal').attr('id')
        // get the next modal to show
        modal = $(target).data('modal');

        // on hide
        $('#'+modalId).on('hidden.bs.modal', function () {
            // show the next modal
            $('#'+modal).modal('show');
        });
    });

    /**
    *
    * copy code
    *
    */
    $(window).on('copy-click', function(e, target) {
        var code = $("#widget-page-code").select();
          document.execCommand("copy");
          $(target).css("background-color","#474747");
          $(target).css("border","#474747");
    });

    /**
     * Toggle dates
     */
    $(window).on('toggle-date-init', function(e, target) {
            if(window.location.href.indexOf('range') == -1) {
             $('.picker').css('display','none');
            }
            else {
                $('#view').css('margin-top','5px');
            }

            var a = $('#start').val();
            var b = $('#end').val();
            if ((a > b) && (b != '')){
                toastr.error('End date cannot be earlier than the Start date');
                return false;
            }
        });

    /**
    *
    * dropdown fly-out
    *
    */
    $(window).on('dropdown-flyout-init', function(e, target) {
        $(target).hover(function() {
            $('.nav-flyout').show();
            $('.tooltip-credits').hide();
            $('.nav-flyout').mouseleave(function(){
                $('.nav-flyout').hide();
            });

            $(window).click(function() {
                $('.nav-flyout').hide();
            });
        });
    });

    /**
    *
    * dropdown fly-out
    *
    */
    $(window).on('navbar-menu-scroll-init', function(e, target) {
        var height = $(window).height() - 95;
        $(target).height(height + 'px').css('overflow-y', 'scroll');
    });

    /**
     * Toggle Date Init
     */
    $(window).on('toggle-date-init', function(e, target) {
        if(window.location.href.indexOf('range') == -1) {
            $('.picker').css('display','none');
        }
        else {
            $('#view').css('margin-top','5px');
        }

        var a = $('#start').val();
        var b = $('#end').val();
        if ((a > b) && (b != '')){
            toastr.error('End date cannot be earlier than the Start date');
            return false;
        }
    });

    /**
     * Get position's total openings per location
     */
    $(window).on('research-top-location-init', function(e, target) {
        var target = $(target);

        // set parameters
        var data = {
            position : target.data('position'),
            location : target.data('location')
        }

        // get response template
        $.get('/ajax/research/location', data, function(response) {
            $('.top-locations').html(response);
        })
    });

    /**
    *
    * Restore inactive actions
    */
    $(window).on('restore-button-init', function(e, target) {
        if(window.location.href.indexOf("?filter[form_active]=0") > -1) {
            $('a.btn.btn-default.restore.hide').removeClass('hide');
            $('.breadcrumbs ul li a[title="Inactive"]').removeClass('hide');
            $('.remove a.btn.btn-default.remove').attr('data-do','form-permanent-remove');
        }
        if(window.location.href.indexOf("?filter[form_active]=1") > -1) {
            $('.breadcrumbs ul li a[title="Active"]').removeClass('hide');
        }
    });

    /**
    *
    * Restore inactive actions
    *
    */
    $(window).on('restore-button-init', function(e, target) {
        if(window.location.href.indexOf("?filter[form_active]=0") > -1) {
            $('a.btn.btn-default.restore.hide').removeClass('hide');
            $('.breadcrumbs ul li a[title="Inactive"]').removeClass('hide');
            $('.breadcrumbs ul li a[title="Active"]').css({'display':'none'});
            $('.remove a.btn.btn-default.remove').attr('data-do','form-permanent-remove');
        }
        if(window.location.href.indexOf("?filter[form_active]=1") > -1) {
            $('.breadcrumbs ul li a[title="Active"]').removeClass('hide');
        }
        if(window.location.href.indexOf("l/profile/tracking/application/poster/search") !== 0) {
            $('.breadcrumbs ul li a[title="Active"]').removeClass('hide');
        }
    });


    /**
    *
    * Trigger upload csv
    *
    */
    $(window).on('upload-csv-click', function(e, target) {
        $('#file_csv').trigger('click');
    });

    /**
    *
    * Listen if file uploaded
    *
    */
    $(window).on('upload-csv-init', function(e, target) {
        $(target).tooltip();
        $("#file_csv").change(function(e){
            this.form.submit();
        });
    });


    /**
    *
    * Custom toart for interested seeker
    *
    */
    $( document ).ready(function() {
        var container = $("<div>");
        var img = $("<img src='/images/interested-seeker.png'/>");

        container.addClass("toastr");
        container.addClass("interested-seeker");
        container.append(img);
        $("body").append(container);
    });

    /**
    *
    * toggle hide and show featured pages
    *
    */
    $(window).on('featured-pages-click', function(e, target) {
        // check if width is for mobile (768px)
        if ($( document ).width() <= '768') {
            var target = $(target);
            $(target).closest(".col-md-2").find("ul").toggleClass('show');
            $(target).find("i").toggleClass('fa-caret-down fa-caret-up');
        }
    });

    //activate all scripts
    $(document.body).doon();
});
