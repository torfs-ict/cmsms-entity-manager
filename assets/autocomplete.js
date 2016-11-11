(function($) {
    $(document).ready(function() {
        $('[data-autocomplete]').each(function(index, el) {
            var $el = $(el);
            $el.autocomplete({
                source: $el.attr('data-autocomplete')
            });
        });
    });
})(jQuery);