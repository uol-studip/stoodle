(function ($) {
    function getTime (time) {
        var fragments = time.split(':'),
            date      = new Date();
        date.setHours(parseInt(fragments[0], 10));
        date.setMinutes(parseInt(fragments[1], 10));
        return date;
    }
    
    $.timepicker.regional['de'] = {
        closeText: 'Schliessen',
        currentText: 'Jetzt',
        hourText: 'Stunde',
        minuteText: 'Minute',
        timeText: 'Uhrzeit'
    }
    $.timepicker.setDefaults($.timepicker.regional['de']);
    $.datepicker.setDefaults($.datepicker.regional['de']);

    $.fn.init_input = function (type) {
        this.each(function () {
            $(this).removeClass('hasDatepicker').nextAll('[type=hidden]').remove();

            if (type === 'text') {
                $(this).val('');
                return;
            }

            var that = $(this),
                name = $(this).attr('name'),
                hidden_input = $('<input type="hidden"/>').attr('name', name),
                trigger, time,
                options = {
                    changeMonth: true,
                    changeYear: true,
                    minDate: new Date(),
                    numberOfMonths: 2,
                    onSelect: function (picker) {
                        var date, additional, time;
                        if ($(this).attr('type') === 'time') {
                            date = getTime(picker);
                        } else {
                            date = $(this)[(type === 'range' ? 'datetime' : type) + 'picker']('getDate')
                        }
                        if ($(this).is('[name^=option]') && type === 'range') {
                            additional = $(this).siblings('span').find('input.hasDatepicker');
                            if (!additional.data('changed')) {
                                additional.datepicker('setDate', new Date(date.getTime() + 2 * 60 * 60 * 1000));
                                additional.datepicker('option', 'minDate', date);
                            }
                        }

                        time = Math.floor(date.getTime() / 1000);
                        $(this).next().val(time);
                        
                        $(this).data('changed', true);
                    },
                    timeOnly: type === 'time'
                    // ,
                    // addSliderAccess: true,
                    // sliderAccessArgs: { touchonly: false }
                };

            if (type === 'time') {
                delete options.minDate;
            } else if (type !== 'date' && type !== 'datetime' && type !== 'range') {
                throw 'Invalid type argument: ' + type;
            }

            $(this)[(type === 'range' ? 'datetime' : type) + 'picker'](options);

            if ($(this).val()) {
                time = type === 'time'
                     ? getTime($(this).val())
                     : $(this)[(type === 'range' ? 'datetime' : type) + 'picker']('getDate');

                if (time) {
                    time = Math.floor(time.getTime() / 1000);
                }
            }
        
            hidden_input.val(time || null);
            $(this).after(hidden_input)

        });

        return this;
    }    
}(jQuery));

jQuery(function ($) {
    function pad(what, length) {
        return ('0000000000' + what).substr(-(length || 2));
    }

    $('.dates input[type=checkbox]').on('click', function () {
        var input = $(this).parent().siblings('[type=datetime]').attr('disabled', this.checked);
        if (input.is(':not(:disabled)')) {
            input.focus();
        }
    }).filter(':checked').each(function () {
        $(this).parent().siblings('[type=datetime]').attr('disabled', true);
    });

/*
    $('form[action*="admin/edit"]').on('click', 'button[name=add]', function () {
        console.log('foo');

        var row   = $('tbody.options tr:last').prev(),
            clone = row.clone(false, false),
            input = $('input:not([type=hidden])', clone),
            index = 1 + parseInt($.trim($('td:first', row).text()).substr(1), 10);
        $('input', clone).attr('name', 'options[-' + (new Date()).getTime() + ']');
        
        input.attr('id', null);

        $('td:first', clone).text('#' + index);
        $('button[name=remove]', clone).val(index);
        clone.toggleClass('cycle_even cycle_odd');
        row.after(clone);

        $('select#type').change();

        return false;
    });
*/
    
    $('form[action*="admin/edit"]').on('click', 'button[name=remove]', function () {
        var row = $(this).closest('tr');
        row.nextAll().not(':last').toggleClass('cycle_even cycle_odd').each(function () {
            var index = parseInt($.trim($('td:first', this).text()).substr(1), 10) - 1;
            $('td:first', this).text('#' + index);
            $('button[name=remove]', this).val(index - 1);
//            $('input[name^="options"]', this).attr('name', 'options[' + (index - 1) + ']');
        });
        
        if (row.siblings().length === 2) {
            $('input', row).val('');
        } else {
            row.remove();
        }

        return false;
    });

    $('input#start_date, input#end_date').init_input('datetime');

    $('select#type').change(function () {
        var type     = $(this).val(),
            elements = $('tbody.options tr:not(:last):not(:first)');

        elements.each(function (index) {
            var original     = $('input:not([type=hidden])', this).first(),
                orig_type    = original.attr('type'),
                value        = original.val(),
                clone, temp,
                defaultValue = $('input[type=hidden]', this).val();
            
            try {
                original[(orig_type === 'range' ? 'datetime' : orig_type) + 'picker']('destroy');
            } catch (e) {}

            clone = original.clone(false, false);
            clone.attr('type', type);
            
            if (value && (orig_type === 'datetime' || orig_type === 'range') && type === 'date') {
                clone.val(value.split(' ')[0]);
            } else if (value && (orig_type === 'datetime' || orig_type === 'range') && type === 'time') {
                clone.val(value.split(' ')[1]);
            } else if (value && orig_type === 'time' && (type === 'datetime' || type === 'range')) {
                temp = new Date();
                temp = pad(temp.getDate()) + '.' + pad(temp.getMonth() + 1) + '.' + (2000 + temp.getYear() % 100);
                clone.val(temp + ' ' + value);
            } else if (value && orig_type === 'date' && (type === 'datetime' || type === 'range')) {
                temp = new Date();
                temp = pad(temp.getHours()) + ':' + pad(temp.getMinutes());
                clone.val(value + ' ' + temp);
            } else if (value && orig_type !== type) {
                clone.val('');
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
