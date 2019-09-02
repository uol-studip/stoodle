<?php
namespace Stoodle;

use PageLayout;
use URLHelper;

class Plugin extends \StudIPPlugin
{
    protected function addScript($script)
    {
        $script = ltrim($script, '/');
        $url = URLHelper::getURL(rtrim($this->getPluginURL(), '/') . "/{$script}", [
            'v' => $this->getPluginVersion(),
        ]);
        PageLayout::addScript($url);
    }

    protected function getPluginVersion()
    {
        static $manifest = null;
        if ($manifest === null) {
            $manifest = $this->getMetadata();
        }
        return $manifest['version'];
    }
}
