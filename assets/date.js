(function($) {
    $(document).ready(function() {
        $('[data-date]').each(function(index, el) {
            var $el = $(el);
            var $alt = $el.next('.entity.date-alt');
            $el.datepicker({
                altField: $alt,
                altFormat: '@',
                constrainInput: true,
                dateFormat: 'dd-mm-yy',
                showAnim: 'slideDown',
                showButtonPanel: true
            }).datepicker('setDate', $.datepicker.parseDate('@', $alt.val() * 1000));
        });
    });
})(jQuery);