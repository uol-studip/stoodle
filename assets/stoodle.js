(function ($) {
    $('#comments legend').live('click', function () {
        $(this).closest('fieldset').toggleClass('closed');
        return false;
    });
    
    $('.stoodle-participants').live('click', function () {
        $(this).toggleClass('collapsed').next().toggle('blind', 'fast');
        return false;
    });
    
    $(':checkbox[name="mail_to[]"]').live('click', function () {
        if ($(this).val() === 'all') {
            $(':checkbox[name="mail_to[]"]:not(:disabled)').attr('checked', this.checked);
        } else if (!this.checked) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', false);
        } else if ($(':checkbox[name="mail_to[]"]:not([value=all]):not(:disabled):not(:checked)').length === 0) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', true);
        }
    });
}(jQuery));

jQuery(function ($) {
    $('#comments legend').click().wrapInner('<a href="#"/>');
    $('.stoodle-participants').wrapInner('<a href="#"/>').addClass('collapsed').next().hide();
});