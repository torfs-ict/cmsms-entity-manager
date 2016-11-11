(function($) {
    $(document).ready(function() {
        $('[data-tags]').each(function(index, el) {
            var $el = $(el);
            $el.tagit({
                autocomplete: {
                    source: $el.attr('data-tags'),
                },
                allowSpaces: true,
                removeConfirmation: true
            });
        });
    });
})(jQuery);