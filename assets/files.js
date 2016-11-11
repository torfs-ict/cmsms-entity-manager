(function($) {
    $(document).ready(function() {
        if ($('.entity-files').length < 1) return;
        $('.entity-files').each(function(index, el) {
            var $dz = $(el).find('.dropzone');
            $dz.dropzone({
                url: $dz.attr('data-url'),
                clickable: '.dz-message',
                maxFiles: 1,
                init: function() {
                    this.on('addedfile', function(file) {
                        $(this.element).find('.dz-progress > span').text('0');
                        $(this.element).find('.dz-progress').fadeIn();
                        $(this.element).attr('title', file.name);
                    });
                    this.on('success', function(file, response) {
                        $(this.element).find('.dz-progress').hide();
                        $(this.element).attr('title', '');
                        $(this.element).find('.dz-download').fadeIn();
                        var $dl = $(this.element).find('.dz-download > a');
                        $dl.attr('href', response);
                        $dl.text(file.name);
                        this.removeAllFiles();
                    });
                    this.on('uploadprogress', function(file, percentage, bytesSent) {
                        $(this.element).find('.dz-progress > span').text(parseInt(percentage));
                    });
                }
            });

            return;
            var dropzone = $dz.get(0).dropzone;
            var minSteps = 6,
                maxSteps = 600,
                timeBetweenSteps = 100,
                bytesPerStep = 1000;

            dropzone.uploadFiles = function(files) {
                var self = this;
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    totalSteps = Math.round(Math.min(maxSteps, Math.max(minSteps, file.size / bytesPerStep)));
                    for (var step = 0; step < totalSteps; step++) {
                        var duration = timeBetweenSteps * (step + 1);
                        setTimeout(function(file, totalSteps, step) {
                            return function() {
                                file.upload = {
                                    progress: 100 * (step + 1) / totalSteps,
                                    total: file.size,
                                    bytesSent: (step + 1) * file.size / totalSteps
                                };
                                self.emit('uploadprogress', file, file.upload.progress, file.upload.bytesSent);
                                if (file.upload.progress == 100) {
                                    file.status = Dropzone.SUCCESS;
                                    self.emit("success", file, 'success', null);
                                    self.emit("complete", file);
                                    self.processQueue();
                                }
                            };
                        }(file, totalSteps, step), duration);
                    }
                }
            }
        });
    });
})(jQuery);