(function ($) {
    $('#comments legend').live('click', function () {
        $(this).closest('fieldset').toggleClass('closed');
        return false;
    });
    
    $('.stoodle-participants').live('click', function () {
        $(this).toggleClass('collapsed').next().toggle('blind', 'fast');
        return false;
    });
}(jQuery));

jQuery(function ($) {
    $('#comments legend').click().wrapInner('<a href="#"/>');
    $('.stoodle-participants').wrapInner('<a href="#"/>').addClass('collapsed').next().hide();
});