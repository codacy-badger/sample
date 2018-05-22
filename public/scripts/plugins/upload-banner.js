$(function() {
    $('.change-bgcolor-btn').on('click', function(e) {
        $(this).addClass('hide');
        $('.change-bgcolor').removeClass('hide');
    });

    $('.cancel-bgcolor').on('click', function(e) {
        $('.change-bgcolor').addClass('hide');
        $('.change-bgcolor-btn').removeClass('hide');
    });

    $('.post-profile-body').css('background-color', $('#profile-bgc').val());
    $('#colorpicker').colorpicker();
    $('#profile-bgc').on('change', function(e) {
        $('.post-profile-body').css('background-color', $(this).val());
    });

    var c = function() {
        return this.__construct.call(this);
    }, p = c.prototype;

    /* Public Properties
     --------------------------------- */
    /* Construct Method
     --------------------------------- */
    p.__construct = function() {
        this.__listen();
    };

    /* Public Methods
     --------------------------------- */
    p.__listen = function() {
        // upload event
        $('html body').on('change', 'input.banner-input', this.displayImage.bind(this));
        $('a.cancel-update').click(this.cancelUpdateCover.bind(this));
    };

    p.displayImage = function(e) {
        var self    = this;
        var target  = e.target || window.eventsrcElement;
        var files   = target.files;

        if(!FileReader || !files || !files.length) {
            return this;
        }

        var reader  = new FileReader();
        var i       = 0;

        reader.onload = function() {
            var img = new Image();
            img.src = reader.result;

            img.onload = function() {
                var width = img.width;
                var height = img.height;

                if (width > height) {
                    while (width > 1000) {
                        width  = width * 0.9;
                        height = height * 0.9;
                    }
                } else {
                    while (height > 1000) {
                        width  = width * 0.9;
                        height = height * 0.9;
                    }
                }

                var canvas    = document.createElement('canvas');
                canvas.width  = width;
                canvas.height = height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);
                window.cUpdate = function(x, y, width, height) {
                    ctx.drawImage(img, 0, 0, width, height, x, y, width, height);
                    var imgSrc  = canvas.toDataURL('image/jpeg');
                    $('img', container).attr('src', imgSrc).css('top', 0);
                };
                var imgSrc  = canvas.toDataURL('image/jpeg');

                var tpl     = $('script#image-tpl').html();
                var tpl     = tpl.replace(/\[SRC\]/g, imgSrc);

                var container = $('div.profile-banner-container');
                var form      = $('form.banner-form');
                var save_container = $('div.wrapper');

                // reset banner images
                container.find('img.original-banner').addClass('hide');
                container.find('img.banner-img').remove();
                container.find('a.update-banner').addClass('hide');

                container.prepend(tpl);
                form.find('input[name="profile_banner"]').val(imgSrc);

                container.find('img.banner-img')
                        .css('cursor', 'move')
                        .draggable({
                            axis : 'y',
                            scroll : false,
                            drag : function(event, ui) {
                                var y1 = container.height();
                                var y2 = container.find('img.banner-img').height();

                                if (ui.position.top >= 0) {
                                    ui.position.top = 0;
                                } else if (ui.position.top <= (y1 - y2)) {
                                    ui.position.top = y1 - y2;
                                }
                            },
                            stop : function(event, ui) {
                                var position = ui.position.top;
                                var ratio =  height / $('img', container).height();

                                canvas.height = height - Math.abs(position);
                                ctx.drawImage(img, 0, position * ratio, width, height);

                                var imgSrc  = canvas.toDataURL('image/jpeg');
                                form.find('input[name="profile_banner"]').val(imgSrc);
                            }
                        });

                // show form
                save_container.find('span.help-text').removeClass('hide');
                form.removeClass('hide');
            };
        };

        reader.readAsDataURL(files[i]);

        return this;
    };

    p.cancelUpdateCover = function(e) {
        e.preventDefault();

        var container = $('div.profile-banner-container');
        var form      = $('form.banner-form');
        var save_container = $('div.wrapper');

        // show original banner
        container.find('a.update-banner').removeClass('hide');
        container.find('img.original-banner').removeClass('hide');
        container.find('img.banner-img').remove();
        container.find('p.text').addClass('hide');
        save_container.find('span.help-text').addClass('hide');

        // hide container
        form.addClass('hide');


         //return this;
        setTimeout(function () {
            return window.location.reload();
        }, 10);
    };

    /* Init
     --------------------------------- */
    return new c();
});