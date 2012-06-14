(function ($) {
    $('#comments legend').live('click', function () {
        $(this).closest('fieldset').toggleClass('closed');
        return false;
    });
}(jQuery));

jQuery(function ($) {
    $('#comments legend').click().wrapInner('<a href="#"/>');
});