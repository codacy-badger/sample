jQuery(function($) {

    /**
     * Position
     */
    (function() {
        $(window).on('hide-fields-init', function(e, target) {
            var type = $(target).val();

            if(type == 'parent') {
                $('#parent').css('display', 'none');
                $('#skills').css('display', 'block');
                $('#detail').css('display', 'block');
            } else {
                $('#parent').css('display', 'block');
                $('#skills').css('display', 'none');
                $('#detail').css('display', 'none');
            }
        });

        $(window).on('hide-fields-change', function(e, target) {
            var type = $(target).val();

            if(type == 'parent') {
                $('#parent').css('display', 'none');
                $('#skills').css('display', 'block');
                $('#detail').css('display', 'block');
            } else {
                $('#parent').css('display', 'block');
                $('#skills').css('display', 'none');
                $('#detail').css('display', 'none');
            }
        });
    })();

    $(window).on('check-date-submit', function(e, target) {
        var a = $('#start').val();
        var b = $('#end').val();

        if ((a > b) && (b != '')) {
            toastr.error('End date cannot be earlier than the Start date');
            return false;
        }
    });

    // check on load if view is true then disable all the input
    $(document).ready(function() {
        const className = 'view';
        if ($('.view').length > 0) {
            $('.view input').prop("disabled", true);
            $('.view textarea').prop("disabled", true);
            $('.view select').prop("disabled", true);
        }
    });
    /**
     * filter event
     */
    (function() {

        $(window).on('show-select-init', function(e, target) {
            $('.filter-form select:not(:first-child)').css('display', 'none');

            var field = $(target).val();
            $('.'+field).css('display', 'block');
            $('.'+field).attr('name', 'filter['+field+']');
        });

        $(window).on('show-select-change', function(e, target) {
            $('.filter-form select:not(:first-child)').css('display', 'none');
            $('.filter-form select').removeAttr('name');
            var field = $(target).val();
            $('.'+field).css('display', 'block');
            $('.'+field).attr('name', 'filter['+field+']');
        });

        // General redirect based on filter change
        $(window).on('redirect-filter-change', function(e, target) {
            // Gets the current path
            var pathname = window.location.pathname;

            // Checks if the path has a query already
            if (~pathname.toLowerCase().indexOf("?")) {
                var queryIndex = pathname.toLowerCase().indexOf("?");
                $queryIndex -= 1;
                pathname = pathname.substr(0, $queryIndex);
            }

            // Gets the select element
            var selectedOption = $(target).children(':selected').attr('selected', true);

            // Gets the filter
            var filterType = $(target).attr('name');

            // Gets the value of the selected filter
            var filterValue = $(selectedOption).val();

            // Checks if value is empty
            if (filterValue != '') {
                // Constructs the path to redirect
                pathname += '?' + filterType + '=' + filterValue;
            }

            // Redirects to said path
            window.location = pathname;
        });

        $(window).on('show-filter-change', function(e, target) {
            // Gets the select element
            var selectedOption = $(target).children(':selected').attr('selected', true);

            // Gets the filter
            var filterType = $(target).attr('name');

            // Gets the value of the selected filter
            var filterValue = $(selectedOption).val();
        });

    })();

    /**
     * filter event
     */
    (function() {
        $(window).on('enlarge-image-click', function(e, target) {
            var image = $(target).data('image');
            $('#image-modal .modal-body').html('<img src="'+image+'" width="100%"/>');
            $('#image-modal').modal('show');
        });

    })();

    /**
     * General Forms
     */
    (function() {

        /**
         * Generate Rate Template
         */
        $(window).on('rate-template-init', function(e, target) {
            var target = $(target);
            var name = $(target).data('name');

            $('.add-rate').click(function () {
                var firstRate = target.find('.rate-template').first().clone(true);
                var lastRate= target.find('.rate-template').last();
                var count = target.find('.rate-template').length;

                firstRate.find('select').attr('name', 'research_location['+ name + '][' + count + '][year]');
                firstRate.find('input').attr('name', 'research_location['+ name + '][' + count + '][rate]');
                firstRate.addClass('added').append('<a class="remove-tag" \
                    href="javascript:void(0);">&#x2716;</a>').insertAfter(lastRate)
                    .find('input[type="text"]').val("");
            });
        });

        /**
         * Generate Rate Template
         */
        $(window).on('hiring-rate-init', function(e, target) {
            var target = $(target);
            var name = $(target).data('name');

            $('.add-hiring-rate').click(function () {
                var firstRate = target.find('.rate-template').first().clone(true);
                var lastRate= target.find('.rate-template').last();
                var count = target.find('.rate-template').length;

                firstRate.find('select').attr('name', 'research_location['+ name + '][' + count + '][year]');
                firstRate.find('input').attr('name', 'research_location['+ name + '][' + count + '][rate]');
                firstRate.addClass('added').append('<a class="remove-tag" \
                    href="javascript:void(0);">&#x2716;</a>').insertAfter(lastRate)
                    .find('input[type="text"]').val("");

                $('.removed').bind('click', this.removeTag);

            });
        });

        $(document).on('click', '.remove-tag', function(e) {
            $(this).parent().remove();
            return false;
        });

        /**
         * Role Init
         */
        $(window).on('role-check-all-init', function(e, target) {
            // get target element
            var target = $(target);
            // get target action
            var action = target.attr('data-target');

            // get all checkboxes
            var checkboxes = $('ul[data-id="' + action + '"]').find('input:checkbox');

            // set default to checked
            target.prop('checked', true);

            // loop through checkboxes
            checkboxes.each(function(index, element) {
                if (!$(element).is(':checked')) {
                    target.prop('checked', false);
                }
            });
        });

        /**
         * Role change
         */
        $(window).on('role-check-all-change', function(e, target) {
            // get target element
            var target = $(target);
            // get target action
            var action = target.attr('data-target');

            // get all checkboxes
            var checkboxes = $('ul[data-id="' + action + '"]').find('input:checkbox');

            checkboxes.each(function(index, element) {
                if (target.is(':checked')) {
                    $(element).prop('checked', true);
                    return true;
                }

                $(element).prop('checked', false);
            });
        });

        /**
         * Role Item Change
         */
        $(window).on('role-check-item-change', function(e, target) {
            // get target element
            var target = $(target);

            var parent = target.parents('.actions').attr('data-id');

            // get all checkboxes
            var checkboxes = $('ul[data-id="' + parent + '"]').find('input:checkbox');

            // page
            var page = $('input[data-target="' + parent + '"]');

            // set default to checked
            page.prop('checked', true);

            // loop through checkboxes
            checkboxes.each(function(index, element) {
                if (!$(element).is(':checked')) {
                    page.prop('checked', false);
                }
            });
        });

        /**
         * Generate Slug
         */
        $(window).on('generate-slug-keyup', function(e, target) {
            var input = $(target).data('name');
            var slug = $(target).val().toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '')
            .replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g,
                function(s){
                return s.toUpperCase();
            });           // Trim - from end of text

            //Additional for meta title
            $('input[name="' + input + '"]').val(slug);
            var title = $(target).data('title');
            $('input[name="' + title + '"]').val($(target).val());

            //Additional for feature name
            $('input[name="' + input + '"]').val(slug);
            var feature = $(target).data('feature');
            $('input[name="' + feature + '"]').val($(target).val());
        });

        /**
         * Generate Slug
         */
        $(window).on('generate-title-keyup', function(e, target) {
            var input = $(target).data('title');
            var title= $(target).val().toString();

            $('input[name="' + input + '"]').val(title);
        });

        /**
         * Research Type Form
         */
        $(window).on('research-type-init', function(e, target) {
            var target = $(target);

            // generate form
            var generateForm = function() {
                if($(target).val() == 'location') {
                    $('.location').show();
                    $('.position').hide();
                    $('.position-location').hide();
                } else if($(target).val() == 'position') {
                    $('.position').show();
                    $('.location').hide();
                    $('.position-location').hide();
                } else {
                    $('.position-location').show();
                    $('.position').hide();
                    $('.location').hide();
                }
            };

            //initialize
            generateForm();

            //type change
            target.change(function() {
                generateForm();
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
            field = 'post_tags';
            placeholder = 'Tags';
            if (target.data('field')) {
                field =  target.data('field');
            }

            if (target.data('placeholder')) {
                placeholder =  target.data('placeholder');
            }


            //TEMPLATES
            var tagTemplate = '<div class="tag"><input type="text" class="tag-input'
            + ' text-field" name="'+field+'[]" placeholder="'
            + translations[placeholder]+'" value="" />'
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

        /*
        * Text Fields
        */
        $(window).on('link-field-init', function(e, target) {
            target = $(target);
            field = 'post_tags';
            placeholder = 'Tags';
            if (target.data('field')) {
                field =  target.data('field');
            }

            if (target.data('placeholder')) {
                placeholder =  target.data('placeholder');
            }

            var linkTemplate = '<div class="link"><input type="text" class="form-control '
            + field + '" name="'+field+'[]"' +'" placeholder="Add Link" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';


            var addRemove = function(filter) {
                $('a.remove').click(function() {
                    var val = $('input').val();
                    $(this).parent().remove();
                });
            };

            var initTag = function(filter){
                addRemove(filter);
            }

            $('.add').click(function(e){
                var last = $(this).prev().find('div.link:last');

                if($('input', last).val() !== "") {
                    last = $(linkTemplate);
                    target.append(last);

                    initTag(last);
                }
            });

            //INITIALIZE
            $('div.link', target).each(function() {
                addRemove($(this));
            });

        });

        /**
         * Remove unneccesary fields before submitting the form
         */
        $(window).on('research-form-click', function (e, target) {
            e.preventDefault();
            // get type
            var type = $('select[name="research_type"]').val();

            // check type and remove fields
            switch (type) {
                case 'position':
                    $('.location').remove();
                    $('.position-location').remove();
                    break;
                case 'location':
                    $('.position').remove();
                    $('.position-location').remove();
                    break;
                case 'position-location':
                    $('.position').remove();
                    $('.location').remove();
                    break;
                default:
            }

            // submit form
            $('form[name="research-form"]').submit();
        });

        /**
         * Top CompaniesTag Field
         */
        $(window).on('company-field-init', function(e, target) {
            //translations
            try {
                var translations = JSON.parse($('#top-company-translations').html());
            } catch(e) {
                var translations = {};
            }

            [
                'Company'
            ].forEach(function(translation) {
                translations[translation] = translations[translation] || translation;
            });

            target = $(target);
            field = 'research_position[top_companies]';
            placeholder = 'Companies';
            if (target.data('field')) {
                field =  target.data('field');
            }

            if (target.data('placeholder')) {
                placeholder =  target.data('placeholder');
            }

            //TEMPLATES
            var topCompanyTemplate = '<div class="top-company"><input type="text" class="top-company-input'
            + ' text-field" name="'+field+'[]" placeholder="'
            + translations['Company']+'" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';

            var suggestionTemplate = '<li class="suggestion-item">{VALUE}</li>';

            //SELECTORS

            var type = target.attr('data-type');
            var top = target.attr('data-top');
            var left = target.attr('data-left');
            var width = target.attr('data-width');
            var offsetTop = parseFloat(target.attr('data-offset-top')) || 0;
            var offsetLeft = parseFloat(target.attr('data-offset-left')) || 0;

            var inputSuggestion = $('div.input-suggestion');
            var suggestionList = $('ul', inputSuggestion);

            //REUSABLE
            var loadSuggestions = function(list, callback) {
                suggestionList.html('');
                list.forEach(function(item) {
                    var row = suggestionTemplate.replace('{VALUE}', item.label);

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

                $('input', filter).keypress(function(e) {
                    if(e.keyCode == 13 && prevent) {
                        e.preventDefault();
                    }
                }).keydown(function(e) {
                    prevent = false;
                    if(!inputSuggestion.hasClass('hide')) {
                        switch(e.keyCode) {
                            case 40: //down
                                var next = $('li.hover', inputSuggestion).removeClass('hover').index() + 1;

                                if(next === $('li', inputSuggestion).length) {
                                    next = 0;
                                }

                                $('li:eq('+next+')', inputSuggestion).addClass('hover');

                                return;
                            case 38: //up
                                var prev = $('li.hover', inputSuggestion).removeClass('hover').index() - 1;

                                if(prev < 0) {
                                    prev = $('li', inputSuggestion).length - 1;
                                }

                                $('li:eq('+prev+')', inputSuggestion).addClass('hover');

                                return;
                            case 13: //enter
                                if($('li.hover', inputSuggestion).length) {
                                    $('li.hover', inputSuggestion)[0].click();
                                    prevent = true;
                                }
                                return;
                            case 37:
                            case 39:
                                return;
                        }
                    }

                    if(searching) {
                        return;
                    }

                    setTimeout(function() {
                        if ($('input', filter).val() == '') return;
                        searching = true;
                        var url = '/rest/profile/search';
                        var query = {
                            q: [$('input', filter).val()],
                            filter: {
                                type : 'poster'
                            }
                        };

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
                                } else {
                                    var rows = response.results.rows;
                                    if (rows && rows.length > 0) {
                                        for (var i in rows) {
                                            list.push({
                                                label : rows[i].profile_company,
                                                value : rows[i].profile_company
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
                    $('div.top-company input', target).each(function() {
                        if(currentTagValue === $(this).val()) {
                            count++;
                        }
                    });

                    if(count > 1) {
                        $(this).parent().remove();
                    }
                });
            };

             //INITITALIZERS
            var initKeyword = function(filter) {
                addRemove(filter);
                addResize(filter);

                getSuggestions(filter, function(item) {
                    $('input', filter).val(item.value).trigger('keyup');
                });

                // inactiveInput.hide();

                $('input', filter).blur(function() {
                    if(!this.value) {
                        $(this).next().click();
                        inputSuggestion.addClass('hide');
                    }
                });
            };

            //EVENTS
            target.click(function(e) {
                if($(e.target).hasClass('company-field')) {
                    var last = $('div.top-company:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(topCompanyTemplate);
                        target.append(last);

                        initKeyword(last);
                    }

                    $('input', last).focus();
                }
            });

            inputSuggestion.mouseover(function() {
                $('li', inputSuggestion).removeClass('hover');

                var stop = function(e) {
                    if ($(e.target).hasClass('form-control')
                    || $(e.target).hasClass('input-suggestion')
                    || $(e.target).hasClass('suggestion-list')
                    || $(e.target).hasClass('suggestion-item')) {
                        return;
                    }

                    inputSuggestion.addClass('hide');
                    $(document.body).unbind('mouseover', stop);
                    $(window).trigger('suggestion-mouseout');
                };

                $(document.body).on('mouseover', stop);
            });
            //INITIALIZE
            $('div.top-company', target).each(function() {
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
         * Tags Field
         */
        $(window).on('blog-tags-field-init', function(e, target) {
            //translations
            try {
                var translations = JSON.parse($('#blog-tags-translations').html());
            } catch(e) {
                var translations = {};
            }

            [
                'Tags'
            ].forEach(function(translation) {
                translations[translation] = translations[translation] || translation;
            });

            target = $(target);
            placeholder = 'Companies';
            if (target.data('field')) {
                field =  target.data('field');
            }

            if (target.data('placeholder')) {
                placeholder =  target.data('placeholder');
            }

            //TEMPLATES
            var blogTagsTemplate = '<div class="blog-tags"><input type="text" class="blog-tags-input'
            + ' text-field" name="blog_tags[]" placeholder="'
            + translations['Tags']+'" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';

            var suggestionTemplate = '<li class="suggestion-item">{VALUE}</li>';

            //SELECTORS

            var type = target.attr('data-type');
            var top = target.attr('data-top');
            var left = target.attr('data-left');
            var width = target.attr('data-width');
            var offsetTop = parseFloat(target.attr('data-offset-top')) || 0;
            var offsetLeft = parseFloat(target.attr('data-offset-left')) || 0;

            var inputSuggestion = $('div.input-suggestion');
            var suggestionList = $('ul', inputSuggestion);

            //REUSABLE
            var loadSuggestions = function(list, callback) {
                suggestionList.html('');
                list.forEach(function(item) {
                    var row = suggestionTemplate.replace('{VALUE}', item.label);

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

                $('input', filter).keypress(function(e) {
                    if(e.keyCode == 13 && prevent) {
                        e.preventDefault();
                    }
                }).keydown(function(e) {
                    prevent = false;
                    if(!inputSuggestion.hasClass('hide')) {
                        switch(e.keyCode) {
                            case 40: //down
                                var next = $('li.hover', inputSuggestion).removeClass('hover').index() + 1;

                                if(next === $('li', inputSuggestion).length) {
                                    next = 0;
                                }

                                $('li:eq('+next+')', inputSuggestion).addClass('hover');

                                return;
                            case 38: //up
                                var prev = $('li.hover', inputSuggestion).removeClass('hover').index() - 1;

                                if(prev < 0) {
                                    prev = $('li', inputSuggestion).length - 1;
                                }

                                $('li:eq('+prev+')', inputSuggestion).addClass('hover');

                                return;
                            case 13: //enter
                                if($('li.hover', inputSuggestion).length) {
                                    $('li.hover', inputSuggestion)[0].click();
                                    prevent = true;
                                }
                                return;
                            case 37:
                            case 39:
                                return;
                        }
                    }

                    if(searching) {
                        return;
                    }

                    setTimeout(function() {
                        if ($('input', filter).val() == '') return;
                        searching = true;
                        var url = '/ajax/featured/search';

                        var query = {
                            q: $('input', filter).val(),
                        };

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
                                var rows = response.results;
                                if (rows && rows.length > 0) {
                                    for (var i in rows) {
                                        list.push({
                                            label : rows[i],
                                            value : rows[i]
                                        });
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
            var initTags = function(filter) {
                addRemove(filter);
                addResize(filter);

                getSuggestions(filter, function(item) {
                    $('input', filter).val(item.value).trigger('keyup');
                });


                $('input', filter).blur(function() {
                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        //remove it
                        $(this).next().click();
                    }

                    var count = 0;
                    var currentKeywordValue = $(this).val();
                    $('div.blog-tags input', target).each(function() {
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
                if($(e.target).hasClass('blog-tags-field')) {
                    var last = $('div.blog-tags:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(blogTagsTemplate);
                        target.append(last);

                        initTags(last);
                    }

                    $('input', last).focus();
                }
            });

            //INITIALIZE
            $('div.blog-tags', target).each(function() {
                initTags($(this));
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
        $(window).on('wysiwyg-init', function(e, target) {
            $(target).wysihtml5({
                "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                "emphasis": true, //Italics, bold, etc. Default true
                "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                "html": true, //Button which allows you to edit the generated HTML. Default false
                "link": true, //Button to insert a link. Default true
                "image": true, //Button to insert an image. Default true,
                "color": true, //Button to change color of font
                "blockquote": true, //Blockquote
                toolbar: {
                    "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
                    "emphasis": true, //Italics, bold, etc. Default true
                    "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
                    "html": true, //Button which allows you to edit the generated HTML. Default false
                    "link": true, //Button to insert a link. Default true
                    "image": true, //Button to insert an image. Default true,
                    "color": true, //Button to change color of font
                    "blockquote": true, //Blockquote
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

        $(window).on('s3-file-upload-init', function(e, target) {
            //starting id
            var id = 0;
            //initial config
            var config = { form: {}, inputs: {} };
            //current
            var container = $(target);
            //parent form
            var form = container.parents('form').eq(0);
            //make a file
            var file = $('input[type="file"]', target);

            //if no cdn
            if(form.attr('data-do') !== 'cdn-upload') {
                return;
            }

            //though we upload this with s3 you may be using cloudfront
            config.cdn = form.attr('data-cdn');
            config.progress = form.attr('data-progress');
            config.complete = form.attr('data-complete');

            //form configuration
            config.form['enctype'] = form.attr('data-enctype');
            config.form['method'] = form.attr('data-method');
            config.form['action'] = form.attr('data-action');

            //inputs configuration
            config.inputs['acl'] = form.attr('data-acl');
            config.inputs['key'] = form.attr('data-key');
            config.inputs['X-Amz-Credential'] = form.attr('data-credential');
            config.inputs['X-Amz-Algorithm'] = form.attr('data-algorythm');
            config.inputs['X-Amz-Date'] = form.attr('data-date');
            config.inputs['Policy'] = form.attr('data-policy');
            config.inputs['X-Amz-Signature'] = form.attr('data-signature');

            var upload = function(file) {
                var reader = new FileReader();

                var notifier = $.notify('<div>' + config.progress + '</div>', 'info', 0);

                reader.addEventListener('load', function () {
                    //parse out the base 64 so we can make a file
                    var base64 = reader.result.split(';base64,');
                    var mime = base64[0].split(':')[1];

                    var extension = mimeExtensions[mime] || 'unknown';
                    //this is what hidden will be assigned to when it's uploaded
                    var path = config.inputs.key + (++id) + '.' + extension;

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
                            //append
                            var href = config.cdn + '/' + path;
                            var link = $('<a>')
                                .attr('href', href)
                                .attr('target', '_blank')
                                .html(href)
                                .appendTo(container);

                            notifier.fadeOut('fast', function() {
                                notifier.remove();
                            });

                            $.notify(config.complete, 'success');
                        }
                    });
                });

                if (file) {
                    reader.readAsDataURL(file);
                }
            };

            file.change(function() {
                if(!this.files || !this.files[0]) {
                    return;
                }

                for(var i = 0; i < this.files.length; i++) {
                    upload(this.files[i]);
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
     * Profile Crawled
     */
    $(window).on('profile-crawled-click', function(e, target) {
        if ($(target).is(':checked')) {
            $('.export-button').attr('href', $('.export-button').attr('href')+'&crawled=true');

            return;
        }

        $('.export-button').attr('href', $('.export-button').attr('href').replace(/&crawled=true/g,''));

        return;
    });

    /**
     * Validate Email
     */
    $(window).on('validate-email-click', function(e, target) {
        var id = $(target).data('profile-id');
        var email = $(target).data('profile-email');
        $.ajax({
            url : '/ajax/validate/email',
            type : 'GET',
            data : {
                'profile_id': id, 
                'email': email
            },
            beforeSend: function() {
                // add button spinner
                $(target)
                    .find('.fa')
                    .addClass('fa-spinner fa-pulse fa-1x fa-fw');

                toastr.info('Verifying Email...');
            },
            success : function(response) {
                var response = JSON.parse(response);

                // add remove
                $(target)
                    .find('.fa')
                    .removeClass('fa-spinner fa-pulse fa-1x fa-fw');
                    
                if (response.error) {
                    toastr.error(response.message);
                    return false;                    
                }

                toastr.success(response.message);
                // reload the page
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        });
    });

    /**
     * Export Submit Event
     */
    $(window).on('export-form-click', function(e, target) {
        e.preventDefault();

        var a = $('#start').val();
        var b = $('#end').val();

        // check dates
        if ((a > b) && (b != '')) {
            return toastr.error('End date cannot be earlier than the Start date');
        }

        // get export url
        var target = $(e.target);
        var form = $('.filter-form');
        var data = form.serialize();
        var type = target.attr('data-type');
        var query = window.location.search.substr(1);
        var url = '/control/ajax/' + type + '/export' + '?' + data + '&' + query;

        // process export
        return processExport(url, type);
    });

    /**
     * Export Click Event
     */
    $(window).on('export-link-click', function(e, target) {
        e.preventDefault();

        // get export url
        var target = $(e.target);
        var url = target.attr('href');
        var type = target.attr('data-type');

        // process export
        return processExport(url, type);
    });

    /**
     * Process export
     */
    function processExport(url, type) {
        var processing = false;

        // init sse
        var source = new EventSource('/control/ajax/export/stream?type=' + type);

        // listen to export progress event
        source.addEventListener('export-error', function(e) {
            // set data
            processing = true;
            var message = 'Ooops there\'s an error on export';

            // if valid response
            if (e) {
                // parse data
                var data = JSON.parse(e.data);
                var message = data.message;
            }

            // close source and display message
            source.close();
            toastr.error(message);
        });

        // listen to export progress event
        source.addEventListener('export-progress', function(e) {
            // processing flag
            processing = true;
            // show modal
            $('#exportModal').modal('show');
        });

        // listen to export complete event
        source.addEventListener('export-complete', function(e) {
            // if valid response
            if (e) {
                // parse data
                var data = JSON.parse(e.data);

                // close modal and download file
                $('#exportModal').modal('hide');
                $('body').append('<iframe src="' + data.csv_link + '" style="display: none;"></iframe>');
            } else {
                // display message
                toastr.error('Ooops there\'s an error on export');
            }

            // close event source
            source.close();
        });

        // send export request
        $.get(url, function(response, status) {
            var data = JSON.parse(response);

            // if error
            if (data && data.error) {
                // display message
                return toastr.error(data.message);
            }

            // if no running worker
            setTimeout(function() {
                if (!processing) {
                    // display message
                    toastr.error('No running worker please contact admin');
                    source.close();
                }
            }, 3000);
        });

        return false;
    }

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

    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    /**
     *   Profile Image tooltip
     */
    $(window).on('image-tooltip-init', function (e, target) {
        $('.profile-image-div').hover(function(){
            $('.profile-image-tooltip').show();
        });
        $('.profile-image-div').mouseleave(function(){
            $('.profile-image-tooltip').hide();
        });
        $('.banner-add-div').hover(function(){
            $('.banner-add-tooltip').show();
        });
        $('.banner-add-div').mouseleave(function(){
            $('.banner-add-tooltip').hide();
        });
        $('.banner-update-div').hover(function(){
            $('.banner-update-tooltip').show();
        });
        $('.banner-update-div').mouseleave(function(){
            $('.banner-update-tooltip').hide();
        });
    });

    //activate all scripts
    $(document.body).doon();
});
