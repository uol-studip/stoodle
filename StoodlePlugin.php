<?php
/**
 * Stoodle.class.php
 *
 * Shameless doodle clone
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.8
 **/
class StoodlePlugin extends StudIPPlugin implements StandardPlugin
{
    function __construct()
    {
        parent::__construct();

        if (Navigation::hasItem('/course') and $this->isActivated()) {
            $navigation = new Navigation(_('Stoodle'), PluginEngine::getLink('stoodleplugin/stoodle/index'));
            Navigation::addItem('/course/stoodle', $navigation);

            if ($GLOBALS['perm']->have_studip_perm('tutor', Request::option('cid'))) {
                $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), PluginEngine::GetLink('stoodleplugin/stoodle/index')));
                $navigation->addSubNavigation('administration', new Navigation(_('Konfiguration'), PluginEngine::GetLink('stoodleplugin/admin')));
            }
        }
    }

    public function initialize()
    {
        require 'bootstrap.php';
        
        require 'app/models/Stoodle.php';
        require 'app/models/StoodleOption.php';
        require 'app/models/StoodleComment.php';
        require 'app/models/StoodleAnswer.php';

        $this->addStylesheet('assets/stoodle.less');
        if (Studip\ENV === 'development') {
            PageLayout::addScript($this->getPluginURL() . '/assets/jquery-ui-timepicker-0.9.9.js');
        } else {
            PageLayout::addScript($this->getPluginURL() . '/assets/jquery-ui-timepicker-0.9.9.min.js');
        }
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
