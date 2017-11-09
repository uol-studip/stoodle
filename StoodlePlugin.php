<?php
/**
 * Stoodle.class.php
 *
 * Shameless doodle clone
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.9.6.5
 **/
class StoodlePlugin extends StudIPPlugin implements StandardPlugin
{
    const GETTEXT_DOMAIN = 'stoodle';

    public function __construct()
    {
        parent::__construct();

        bindtextdomain(static::GETTEXT_DOMAIN, $this->getPluginPath() . '/locale');
        bind_textdomain_codeset(static::GETTEXT_DOMAIN, 'ISO-8859-1');
    }

    /**
     * Plugin localization for a single string.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string String to translate
     * @return translated string
     */
    public function _($string)
    {
        $result = static::GETTEXT_DOMAIN === null
                ? $string
                : dcgettext(static::GETTEXT_DOMAIN, $string, LC_MESSAGES);
        if ($result === $string) {
            $result = _($string);
        }

        if (func_num_args() > 1) {
            $arguments = array_slice(func_get_args(), 1);
            $result = vsprintf($result, $arguments);
        }

        return $result;
    }

    /**
     * Plugin localization for plural strings.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string0 String to translate (singular)
     * @param String $string1 String to translate (plural)
     * @param mixed  $n       Quantity factor (may be an array or array-like)
     * @return translated string
     */
    public function _n($string0, $string1, $n)
    {
        if (is_array($n)) {
            $n = count($n);
        }

        $result = static::GETTEXT_DOMAIN === null
                ? $string0
                : dngettext(static::GETTEXT_DOMAIN, $string0, $string1, $n);
        if ($result === $string0 || $result === $string1) {
            $result = ngettext($string0, $string1, $n);
        }

        if (func_num_args() > 3) {
            $arguments = array_slice(func_get_args(), 3);
            $result = vsprintf($result, $arguments);
        }

        return $result;
    }

    protected static $icon_mapping = [
        'clickable'    => 'blue',
        'accept'       => 'green',
        'info_alt'     => 'white',
        'info'         => 'black',
        'inactive'     => 'grey',
        'status-red'   => 'red',
        'status-green' => 'green',
    ];

    public function getTabNavigation($course_id)
    {
        $navigation = new Navigation($this->_('Stoodle'), PluginEngine::getURL('stoodleplugin/stoodle/index'));
        $navigation->setImage($this->getIcon('assessment', 'info_alt'));
        $navigation->setActiveImage($this->getIcon('assessment', 'info'));

        if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            $navigation->addSubNavigation('index', new Navigation(
                $this->_('Übersicht'),
                PluginEngine::getLink($this, [], 'stoodle/index')
            ));
            $navigation->addSubNavigation('administration', new Navigation(
                $this->_('Verwaltung'),
                PluginEngine::GetLink($this, [], 'admin')
            ));
        }
        return ['stoodle' => $navigation];
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        return null;
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return [];
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     */
    public function perform($unconsumed_path)
    {
        require 'bootstrap.php';

        $manifest = $this->getMetadata();
        Helpbar::get()->addPlainText($this->_('Informationen'), $manifest['description']);

        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes');
        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes/stoodle', 'Stoodle');

        $this->addStylesheet('assets/jquery-timepicker/jquery-ui-timepicker-addon.css');
        $this->addStylesheet(
            $this->isLegacy() ? 'assets/stoodle-3.3.less' : 'assets/stoodle.less'
        );

        PageLayout::addScript($this->getPluginURL() . '/assets/date-js/date-de-DE.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-timepicker-addon.js');
//        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-sliderAccess.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle-config.js');

        $range_id = Request::option('cid', $GLOBALS['SessSemName'][1]);

        $app_path = $this->getPluginPath() . '/app';

        URLHelper::removeLinkParam('cid');
        $app_url = rtrim(PluginEngine::getURL($this, [], ''), '/');
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher = new Trails_Dispatcher($app_path, $app_url, 'stoodle');
        $dispatcher->current_plugin = $this;
        $dispatcher->range_id       = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }

    /**
     * Version-safe icon creation.
     * Works in Stud.IP 3.5 and below.
     */
    public function getIcon($icon, $role, $attributes = array())
    {
        if (!$this->isLegacy()) {
            return Icon::create($icon, $role, $attributes);
        }
        return Assets::img(sprintf(
            'icons/16/%s/%s.svg',
            self::$icon_mapping[$role],
            $icon
        ), $attributes);
    }

    private function isLegacy()
    {
        return !class_exists('ActionMenu');
    }
}
