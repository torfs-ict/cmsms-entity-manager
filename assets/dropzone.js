Dropzone.autoDiscover = false;

(function($) {
    $(document).ready(function() {
        $('#addImage').click(function() {
            var el = $($('#addImageTemplate').html().replace(/__index__/g, $('.entity-images .dropzone').length + 1));
            $('.entity-images').append(el);
            el.find('button').button({
                icons: {
                    primary: 'ui-icon-scissors'
                }
            });
            $(document).trigger('dropzone');
        });
        $(document).trigger('dropzone');
    });
    $(document).on('dropzone', function() {
        $('.entity-images .dropzone').each(function(index, el) {
            var $el = $(el);
            if ($el.data('init') == 1) return;
            $el.data('init', 1);
            $el.dropzone({
                url: $el.attr('data-url'),
                maxFiles: 1,
                createImageThumbnails: false,
                init: function() {
                    var $this = $(this.element);
                    var record = $this.parents('[data-record]');
                    this.on('addedfile', function(file) {
                        $this.find('.cropper-spinner').css('display', 'block');
                        var fileReader;
                        fileReader = new FileReader;
                        fileReader.onload = (function(_this) {
                            var img = $this.find('img:not(.cropper-spinner)');
                            var w = img.width();
                            var h = img.height();
                            img.attr('src', fileReader.result).width(w).height(h);
                        });
                        fileReader.readAsDataURL(file);
                    });
                },
                success: function(file, url) {
                    var $this = $(this.element);
                    var img = $this.find('img:not(.cropper-spinner)');
                    var w = img.width();
                    var h = img.height();
                    img.attr('src', url).hide().width(w).height(h).fadeIn();
                    $this.parents('.container').find('input').val('');
                    $this.parents('.container').find('.croplink button').click();
                    $this.parents('.container').addClass('uploaded');
                    this.removeAllFiles();
                }
            });
            var $img = $el.find('img:not(.cropper-spinner)');
            var ratio = $el.attr('data-ratio').split(':');
            var width = parseInt(ratio[0]);
            var height = parseInt(ratio[1]);
            var imgHeight = parseInt($img.attr('data-height'));
            $img.height(imgHeight);
            var imgWidth = (width * imgHeight) / height;
            $img.width(imgWidth);
        });
    });
})(jQuery);