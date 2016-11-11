(function($) {
    var minWidth = 0;
    $(document).ready(function() {
        if ($('.entity-cropper').length < 1) return;
        var setCropData = function(el) {
            var data = $(el).cropper('getData');
            if (data.width < minWidth) return;
            var parent = $(el).parent();
            parent.find('input[name="x"]').val(data.x);
            parent.find('input[name="y"]').val(data.y);
            parent.find('input[name="width"]').val(data.width);
            parent.find('input[name="height"]').val(data.height);
        };
        $('.entity-cropper').each(function(index, el) {
            $(el).find('.dropzone').dropzone({
                url: 'about:blank',
                maxFiles: 1,
                createImageThumbnails: false,
                init: function() {
                    var $this = $(this.element);
                    this.on('addedfile', function(file) {
                        var parent = $this.parent();
                        var fileReader;
                        fileReader = new FileReader;
                        fileReader.onload = (function(_this) {
                            parent.find('img:not(.cropper-spinner)').cropper('reset').cropper('replace', fileReader.result);
                            parent.find('input[name="filename"]').val(file.name);
                            parent.find('input[name="blob"]').val(fileReader.result);
                            this.removeAllFiles();
                        });
                        fileReader.readAsDataURL(file);
                    });
                }
            });
            $(el).find('img').cropper({
                aspectRatio: parseFloat($(el).attr('data-ratio')) || null,
                rotatable: false,
                scalable: false,
                zoomable: true,
                responsive: true,
                movable: false,
                autoCropArea: 1,
                minCropBoxWidth: 50,
                data: {
                    x: parseFloat($(el).find('input[name="x"]').val()),
                    y: parseFloat($(el).find('input[name="y"]').val()),
                    width: parseFloat($(el).find('input[name="width"]').val()),
                    height: parseFloat($(el).find('input[name="height"]').val())
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
                }
            });
        });
    });
})(jQuery);