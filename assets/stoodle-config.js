/*jslint browser: true, unparam: true */
/*global jQuery */
(function ($) {
    'use strict';

    function getTime(time) {
        var fragments = time.split(':'),
            date      = new Date();
        date.setHours(parseInt(fragments[0], 10));
        date.setMinutes(parseInt(fragments[1], 10));
        return date;
    }

    $.timepicker.regional.de = {
        closeText: 'Schliessen',
        currentText: 'Jetzt',
        hourText: 'Stunde',
        minuteText: 'Minute',
        timeText: 'Uhrzeit'
    };
    $.timepicker.setDefaults($.timepicker.regional.de);
    $.datepicker.setDefaults($.datepicker.regional.de);

    $.fn.init_input = function (type) {
        this.each(function () {
            $(this).removeClass('hasDatepicker').nextAll('[type=hidden]').remove();

            if (type === 'text') {
//                $(this).val('');
                return;
            }

            var name = $(this).attr('name'),
                hidden_input = $('<input type="hidden"/>').attr('name', name),
                time,
                options = {
                    changeMonth: true,
                    changeYear: true,
                    minDate: new Date(),
                    hourGrid: 2,
                    minuteGrid: 5,
                    numberOfMonths: 2,
                    onSelect: function (picker) {
                        var date, additional, time;
                        if ($(this).attr('type') === 'time') {
                            date = getTime(picker);
                        } else {
                            date = $(this)[(type === 'range' ? 'datetime' : type) + 'picker']('getDate');
                        }
                        if ($(this).is('[name^=option]') && type === 'range') {
                            additional = $(this).siblings('span').find('input.hasDatepicker');
                            if (!additional.data('changed')) {
                                time = date.getTime() + 2 * 60 * 60 * 1000;
                                additional.datepicker('setDate', new Date(time));
                                additional.datepicker('option', 'minDate', date);
                                additional.next().val(time / 1000);
                            }
                        }

                        time = Math.floor(date.getTime() / 1000);
                        $(this).next().val(time);

                        $(this).data('changed', true);
                    },
                    beforeShow: function (textbox, instance) {
                        instance.dpDiv.css({
                            marginTop: (-textbox.offsetHeight) + 'px',
                            marginLeft: textbox.offsetWidth + 'px'
                        });
                    },
                    timeOnly: type === 'time'
                    // ,
                    // addSliderAccess: true,
                    // sliderAccessArgs: { touchonly: false }
                };

            options.minDate.setSeconds(0);
            options.minDate.setMinutes(0);
            options.minDate.setMilliseconds(0);

            if (type === 'time') {
                delete options.minDate;
            } else if (type !== 'date' && type !== 'datetime' && type !== 'range') {
                throw 'Invalid type argument: ' + type;
            }

            $(this)[(type === 'range' ? 'datetime' : type) + 'picker'](options).blur(function () {
                var value = $(this).val(),
                    date = Date.parse(value);
                if (date !== null) {
                    hidden_input.val((date.getTime() / 1000).toFixed(0));
                }
            });

            if ($(this).val()) {
                time = type === 'time'
                     ? getTime($(this).val())
                     : $(this)[(type === 'range' ? 'datetime' : type) + 'picker']('getDate');

                if (time) {
                    time = Math.floor(time.getTime() / 1000);
                }
            }

            hidden_input.val(time || null);
            $(this).after(hidden_input);
        });

        return this;
    };
}(jQuery));

jQuery(function ($) {
    'use strict';

    function pad(what, length, padding) {
        var str = (typeof what === 'number') ? what.toFixed(0) : what;
        while (str.length < length) {
            str = (padding || 0) + str;
        }
        return str;
    }

    $('input#start_date, input#end_date').init_input('datetime');

    $('.dates input[type=checkbox]').on('click', function () {
        var input = $(this).parent().siblings('.datetime').attr('disabled', this.checked);
        input.filter(':not(:disabled)').focus();
    }).filter(':checked').each(function () {
        $(this).parent().siblings('.datetime').attr('disabled', true);
    });

    $('.stoodle').on('click', 'button[name=remove]', function () {
        var $that    = $(this).attr('disabled', true),
            value    = $that.val(),
            row      = $that.closest('tr'),
            form     = row.closest('form'),
            action   = form.attr('action'),
            formdata = form.serializeArray();

        if (row.siblings().length === 0) {
            row.find('input:not(:checkbox)').val('');
            return false;
        }

        $that.find('img').replaceWith('<span class="ajaxing"/>');

        formdata.push({name: 'remove', value: value});
        $.post(action, formdata, function (response, status, xhr) {
            var options = $('.options', response);
            $('.options', form).replaceWith(options);

            if (!value) {
                $(':checkbox[name="ids[]"][value="all"]').attr('checked', false);
            }

            $('select#type').change();
        });

        return false;
    });

    $('.stoodle').on('click', 'button[name=add]', function () {
        var $that    = $(this).attr('disabled', true),
            form     = $that.closest('form'),
            action   = form.attr('action'),
            formdata = form.serializeArray();

        $('<span/>').addClass('ajaxing').css({verticalAlign: 'top'}).prependTo($that);

        formdata.push({name: 'add', value: ''});
        $.post(action, formdata, function (response, status, xhr) {
            var options = $('.options', response);
            $('.options', form).replaceWith(options);

            $('select#type').change();
        });

        return false;
    });

    $('select#type').change(function () {
        var type     = $(this).val(),
            elements = $('tbody.options tr');

        elements.each(function (index) {
            var original     = $('input:not([type=hidden]):not(:checkbox)', this).first(),
                orig_type    = original.data('type'),
                value        = original.val(),
                clone,
                temp;

            try {
                original[(orig_type === 'range' ? 'datetime' : orig_type) + 'picker']('destroy');
            } catch (e) { }

            clone = original.clone(false, false);
            clone.attr('type', type === 'range' ? 'date' : type);
            clone.data('type', type);

            if (value) {
                if ((orig_type === 'datetime' || orig_type === 'range') && type === 'date') {
                    clone.val(value.split(' ')[0]);
                } else if ((orig_type === 'datetime' || orig_type === 'range') && type === 'time') {
                    clone.val(value.split(' ')[1]);
                } else if (orig_type === 'time' && (type === 'datetime' || type === 'range')) {
                    temp = new Date();
                    temp = pad(temp.getDate()) + '.' + pad(temp.getMonth() + 1) + '.' + (2000 + temp.getYear() % 100);
                    clone.val(temp + ' ' + value);
                } else if (orig_type === 'date' && (type === 'datetime' || type === 'range')) {
                    temp = new Date();
                    temp = pad(temp.getHours()) + ':' + pad(temp.getMinutes());
                    clone.val(value + ' ' + temp);
                } else if (orig_type !== type) {
                    clone.val('');
                }
            }

            original.replaceWith(clone);
            clone.init_input(type);

            if (!$('.type-range', this).toggle(type === 'range').data('stoodled')) {
                $('.type-range', this).data('stoodled', true);
                $('.type-range input:not([type=hidden])', this).init_input('datetime');
            }
        });
    }).change();
});
