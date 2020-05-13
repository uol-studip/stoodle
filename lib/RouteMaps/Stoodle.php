<?php
namespace Stoodle\RouteMaps;

use RESTAPI\RouteMap;

class Stoodle extends RouteMap
{
    /**
     * @get /stoodle/:stoodle_id/options/count
     */
    public function getStoodleOptionCount($stoodle_id)
    {
        $stoodle = \Stoodle\Stoodle::find($stoodle_id);
        if (!$stoodle) {
            $this->notFound("Stoodle with id {$stoodle_id} does not exist");
        }

        return array_map(function ($option) use ($stoodle) {
            return $stoodle->userMayAnswerOption($option->id);
        }, $stoodle->options);
    }
}
