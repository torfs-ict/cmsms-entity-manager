(function($) {
    var minWidth = 0;
    $(document).on('dropzone', function() {
        $('.entity-images p.croplink button').button({
            icons: {
                primary: 'ui-icon-scissors'
            }
        }).click(function(e) {
            e.preventDefault();
            var $this = $(this);
            $this.parents('.container').find('.cropper-spinner').hide();
            var setCropData = function(el) {
                var data = $(el).cropper('getData');
                if (data.width < minWidth) return;
                var parent = $this.parents('.container');
                parent.find('input[name="x"]').val(data.x);
                parent.find('input[name="y"]').val(data.y);
                parent.find('input[name="width"]').val(data.width);
                parent.find('input[name="height"]').val(data.height);
            };
            $this.blur();
            $this.parents('.container').find('.entity-property-cropper').dialog({
                modal: true,
                width: $(window).width() - 50,
                height: $(window).height() - 50,
                title: 'Afbeelding bijsnijden',
                buttons: {
                    'Opslaan': function() {
                        var parent = $this.parents('.container');
                        var data = {
                            x: parseFloat(parent.find('input[name="x"]').val()),
                            y: parseFloat(parent.find('input[name="y"]').val()),
                            width: parseFloat(parent.find('input[name="width"]').val()),
                            height: parseFloat(parent.find('input[name="height"]').val())
                        };
                        $.post(parent.attr('data-url'), data);
                        $(this).dialog('close');
                    },
                    'Annuleren': function() {
                        $(this).dialog('close');
                    }
                },
                close: function(e, ui) {
                    $(this).find('img').cropper('destroy');
                    $(this).dialog('destroy');
                },
                open: function(e, ui) {
                    var data = {
                        x: parseFloat($this.parents('.container').find('input[name="x"]').val()) || 0,
                        y: parseFloat($this.parents('.container').find('input[name="y"]').val()) || 0,
                        width: parseFloat($this.parents('.container').find('input[name="width"]').val()) || 0,
                        height: parseFloat($this.parents('.container').find('input[name="height"]').val()) || 0
                    };
                    var auto = (data.x == 0 && data.y == 0 && data.width == 0 && data.height == 0) ? 1 : false;

                    $(this).find('img').attr('src', $this.parents('.container').find('.dropzone img').attr('src')).cropper({
                        aspectRatio: parseFloat($this.parents('.container').attr('data-ratio')) || null,
                        rotatable: false,
                        scalable: false,
                        zoomable: true,
                        responsive: true,
                        movable: false,
                        autoCropArea: auto,
                        minCropBoxWidth: 50,
                        data: {
                            x: parseFloat($this.parents('.container').find('input[name="x"]').val()),
                            y: parseFloat($this.parents('.container').find('input[name="y"]').val()),
                            width: parseFloat($this.parents('.container').find('input[name="width"]').val()),
                            height: parseFloat($this.parents('.container').find('input[name="height"]').val())
                        },
                        crop: function(e) {
                            if (e.width < minWidth) return;
                            setCropData(this);
                        },
                        cropend: function(e) {
                            var data = $(this).cropper('getData');
                            if (data.width < minWidth) {
                                alert('U dient minstens een gebied van ' + minWidth + ' pixels breed te selecteren.');
                                $(this).cropper('setData', { width: minWidth });
                                e.preventDefault();
                            }
                            setCropData(this);
                        },
                        zoom: function(e) {
                            var data = $(this).cropper('getData');
                            if (data.width < minWidth && e.ratio > 0) {
                                e.preventDefault();
                                alert('U dient minstens een gebied van ' + minWidth + ' pixels breed te selecteren.');
                                while (data.width < minWidth) {
                                    $(this).cropper('zoom', -0.1);
                                    data = $(this).cropper('getData');
                                }
                                $(this).cropper('setData', { width: minWidth });
                                setCropData(this);
                                return false;
                            }
                            setCropData(this);
                        },
                        built: function(e) {
                            var parent = $this.parents('.container');
                            if (parent.hasClass('uploaded')) {
                                parent.removeClass('uploaded');
                                var data = {
                                    x: parseFloat(parent.find('input[name="x"]').val()),
                                    y: parseFloat(parent.find('input[name="y"]').val()),
                                    width: parseFloat(parent.find('input[name="width"]').val()),
                                    height: parseFloat(parent.find('input[name="height"]').val())
                                };
                                $.post(parent.attr('data-url'), data);
                            }
                        }
                    })
                }
            });
        });
    });
})(jQuery);