<?php
if (!function_exists('spoken_time')) {
    function spoken_time($time, $max_displayed = 2)
    {
        $result = [];

        $seconds = $time % 60;
        if ($seconds == 1) {
            $result[] = dgettext(StoodlePlugin::GETTEXT_DOMAIN, '1 Sekunde');
        } else if ($seconds > 0) {
            $result[] = sprintf(dgettext(StoodlePlugin::GETTEXT_DOMAIN, '%u Sekunden'), $seconds);
        }
        $time = (int)($time / 60);

        $minutes = $time % 60;
        if ($minutes == 1) {
            $result[] = dgettext(StoodlePlugin::GETTEXT_DOMAIN, '1 Minute');
        } else if ($minutes > 0) {
            $result[] = sprintf(dgettext(StoodlePlugin::GETTEXT_DOMAIN, '%u Minuten'), $minutes);
        }
        $time = (int)($time / 60);

        $hours = $time % 24;
        if ($hours == 1) {
            $result[] = dgettext(StoodlePlugin::GETTEXT_DOMAIN, '1 Stunde');
        } else if ($hours > 1) {
            $result[] = sprintf(dgettext(StoodlePlugin::GETTEXT_DOMAIN, '%u Stunden'), $hours);
        }
        $time = (int)($time / 24);

        if (empty($result) and $time == 1) {
            $result[] = sprintf(dgettext(StoodlePlugin::GETTEXT_DOMAIN, '%u Stunden'), 24);
        } else if ($time == 1) {
            $result[] = dgettext(StoodlePlugin::GETTEXT_DOMAIN, '1 Tag');
        } else if ($time > 0) {
            $result[] = sprintf(dgettext(StoodlePlugin::GETTEXT_DOMAIN, '%u Tage'), $time);
        }

        return implode(', ', array_slice(array_reverse($result), 0, $max_displayed));
    }
}

StudipAutoloader::addAutoloadPath(__DIR__ . '/lib', 'Stoodle');
StudipAutoloader::addAutoloadPath(__DIR__ . '/models', 'Stoodle');
