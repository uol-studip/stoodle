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
    function getTabNavigation($course_id)
    {
        $navigation = new Navigation(_('Stoodle'), PluginEngine::getURL('stoodleplugin/stoodle/index'));
        $navigation->setImage('icons/16/white/assessment.png');
        $navigation->setActiveImage('icons/16/black/assessment.png');

        if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            $navigation->addSubNavigation('index', new Navigation(_('�bersicht'), PluginEngine::GetLink('stoodleplugin/stoodle/index')));
            $navigation->addSubNavigation('administration', new Navigation(_('Verwaltung'), PluginEngine::GetLink('stoodleplugin/admin')));
        }
        return array('stoodle' => $navigation);
    }

    function getIconNavigation($course_id, $last_visit, $user_id)
    {
        return null;
    }
    
    function getNotificationObjects($course_id, $since, $user_id)
    {
        return array();
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
        require 'bootstrap.php';

        $manifest = $this->getMetadata();
        Helpbar::get()->addPlainText(_('Informationen'), $manifest['description']);

        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes');
        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes/Stoodle', 'Stoodle');

        $this->addStylesheet('assets/jquery-timepicker/jquery-ui-timepicker-addon.css');
        $this->addStylesheet('assets/stoodle.less');

        PageLayout::addScript($this->getPluginURL() . '/assets/date-js/date-de-DE.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-timepicker-addon.js');
//        PageLayout::addScript($this->getPluginURL() . '/assets/jquery-timepicker/jquery-ui-sliderAccess.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle.js');
        PageLayout::addScript($this->getPluginURL() . '/assets/stoodle-config.js');

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
