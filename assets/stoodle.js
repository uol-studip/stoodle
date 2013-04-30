(function ($, STUDIP) {
    var Stoodle = {};

    Stoodle.Comments = {
        init: function () {
            // Enable toggling display of comments
            $('#comments').on('click', ' legend', function () {
                $(this).closest('fieldset').toggleClass('closed');
                return false;
            });

            $('#comments legend').click().wrapInner('<a href="#"/>');
        }
    };
    Stoodle.Result = {
        init: function () {
            // Enable toggling of participants
            $('.stoodle-participants').live('click', function () {
                $(this).toggleClass('collapsed').next().toggle('blind', 'fast');
                return false;
            });

            $('.stoodle-participants').wrapInner('<a href="#"/>').addClass('collapsed').next().hide();
        }
    };
    Stoodle.Overview = {
        init: function () {
            // Enable click on row to select
            $('.stoodle-overview td').click(function () {
                // We need a workaround, since a simple .click() does not suffice
                var href = $(this).closest('tr').find('a[href]').attr('href');
                location.href = href;
                return false;
            });
        }
    };

    $(':checkbox[name="mail_to[]"]').live('click', function () {
        if ($(this).val() === 'all') {
            $(':checkbox[name="mail_to[]"]:not(:disabled)').attr('checked', this.checked);
        } else if (!this.checked) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', false);
        } else if ($(':checkbox[name="mail_to[]"]:not([value=all]):not(:disabled):not(:checked)').length === 0) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', true);
        }
    });

    $('.stoodle-list tbody tr:not(.no-highlight) td:gt(1)').live('mouseenter', function () {
        var index = $(this).index();
        if (index < 2) {
            return;
        }
        $(this).closest('table').find('tbody tr:not(.no-highlight) td:nth-child(' + (index + 1) + ')').addClass('highlighted');
        $(this).closest('table').find('thead th').eq(index - 2).addClass('highlighted');
    }).live('mouseleave', function () {
        var index = $(this).index();
        $(this).closest('table').find('tbody tr:not(.no-highlight) td:nth-child(' + (index + 1) + ')').removeClass('highlighted');
        $(this).closest('table').find('thead th').eq(index - 2).removeClass('highlighted');
    });

    STUDIP.Stoodle = Stoodle;
}(jQuery, STUDIP));

jQuery(document).ready(function ($) {
    STUDIP.Stoodle.Comments.init();
    STUDIP.Stoodle.Result.init();
    STUDIP.Stoodle.Overview.init();
});