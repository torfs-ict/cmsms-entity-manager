(function($) {
    $(document).ready(function() {
        var cfg = $.parseJSON($('#EntityManagerClientConfig').attr('data-config'));
        if (cfg.hidden) {
            $.each(cfg.hidden, function(index, type) {
                $('#content_type option[value="' + type + '"]').remove();
            });
        }
    });
})(jQuery);