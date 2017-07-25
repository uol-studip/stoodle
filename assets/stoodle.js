/*jslint browser: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

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
            $(document).on('click', '.stoodle-participants', function () {
                $(this).toggleClass('collapsed').next().toggle('blind', 'fast');
                return false;
            });

            $('.stoodle-participants').wrapInner('<a href="#"/>').addClass('collapsed').next().hide();
        }
    };
    Stoodle.Overview = {
        init: function () {
            // Enable click on row to select
            $('.stoodle-overview tr:not(.empty) td').click(function () {
                // We need a workaround, since a simple .click() does not suffice
                var href = $(this).closest('tr').find('a[href]').attr('href');
                location.href = href;
                return false;
            });
        }
    };

    $(document).on('click', ':checkbox[name="mail_to[]"]', function () {
        if ($(this).val() === 'all') {
            $(':checkbox[name="mail_to[]"]:not(:disabled)').attr('checked', this.checked);
        } else if (!this.checked) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', false);
        } else if ($(':checkbox[name="mail_to[]"]:not([value=all]):not(:disabled):not(:checked)').length === 0) {
            $(':checkbox[name="mail_to[]"][value=all]').attr('checked', true);
        }
    });

    $(document).on('mouseenter', '.stoodle-list tbody tr:not(.no-highlight) td:gt(1)', function () {
        var index = $(this).index();
        if (index < 2) {
            return;
        }
        $(this).closest('table').find('tbody tr:not(.no-highlight) td:nth-child(' + (index + 1) + ')').addClass('highlighted');
        $(this).closest('table').find('th:nth-child(' + index + ')').addClass('highlighted');
    }).on('mouseleave', '.stoodle-list tbody tr:not(.no-highlight) td:gt(1)', function () {
        var index = $(this).index();
        $(this).closest('table').find('tbody tr:not(.no-highlight) td:nth-child(' + (index + 1) + ')').removeClass('highlighted');
        $(this).closest('table').find('th:nth-child(' + index + ')').removeClass('highlighted');
    }).on('click', '.stoodle-list td:has(:checkbox[name^="selection"])', function (event) {
        if ($(event.target).is('td')) {
            $(':checkbox', this).click();
        }
    });

    STUDIP.Stoodle = Stoodle;

    $(document).ready(function () {
        STUDIP.Stoodle.Comments.init();
        STUDIP.Stoodle.Result.init();
        STUDIP.Stoodle.Overview.init();
    });




    // Use a checkbox as a toggle switch for the disabled attribute of another
    // element. Define element to disable if checkbox is either :checked or
    // :indeterminate by a css selector in attribute "data-disables".
    $(document).on('change', ':checkbox[data-disables]', function () {
        var disables = $(this).data('disables'),
            disabled = $(this).prop('checked') || $(this).prop('indeterminate') || false,
            focussed = $(this).data('gains-focus') !== undefined;
        $(disables).attr('disabled', disabled).trigger('update.proxy');
        if (focussed) {
            $(disables).filter(':not([disabled])').focus();
        }
    }).ready(function () {
        $(':checkbox[data-disables]').trigger('change');
    });

}(jQuery, STUDIP));
