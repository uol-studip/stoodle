<?php
/**
 * Stoodle.class.php
 *
 * Shameless doodle clone
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.9.6
 **/

class StoodlePlugin extends StudIPPlugin implements StandardPlugin
{
    function __construct() 
    {
        parent::__construct();

        if (Navigation::hasItem('/course') and $this->isActivated()) {
            $navigation = new Navigation(_('Stoodle'), PluginEngine::getLink('stoodleplugin/stoodle/index'));
            $navigation->setImage('icons/16/grey/assessment.png');
            $navigation->setActiveImage('icons/16/white/assessment.png');
            Navigation::addItem('/course/stoodle', $navigation);

            if ($GLOBALS['perm']->have_studip_perm('tutor', Request::option('cid'))) {
                $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), PluginEngine::GetLink('stoodleplugin/stoodle/index')));
                $navigation->addSubNavigation('administration', new Navigation(_('Verwaltung'), PluginEngine::GetLink('stoodleplugin/admin')));
            }
        }
    }

    public function initialize()
    {
        require 'bootstrap.php';

        if (!class_exists('Studip\Interactable')) {
            require 'classes/Interactable.class.php';
        }
        if (!class_exists('Studip\Button')) {
            require 'classes/Button.class.php';
        }
        if (!class_exists('Studip\LinkButton')) {
            require 'classes/LinkButton.class.php';
        }
        if (!class_exists('Studip\ResetButton')) {
            require 'classes/ResetButton.class.php';
        }

        PageLayout::addStylesheet($this->getPluginURL() . '/assets/buttons.css');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/tooltipicon.css');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/zebra.css');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-timepicker-addon.css');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/stoodle.css');

        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-timepicker-addon.js');
//        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-ui-sliderAccess.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle-config.js');
    }

    function getIconNavigation($course_id, $last_visit)
    {
        return null;
    }

    function getInfoTemplate($course_id)
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
        $range_id = Request::option('cid', $GLOBALS['SessSemName'][1]);

        $app_path = $this->getPluginPath() . '/app';

        URLHelper::removeLinkParam('cid');
        $app_url = rtrim(PluginEngine::getURL($this, array(), ''), '/');
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher = new Trails_Dispatcher($app_path, $app_url, 'stoodle');
        $dispatcher->plugin   = $this;
        $dispatcher->range_id = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }
}
