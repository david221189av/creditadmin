<?php

namespace Terranet\Administrator\Providers\Handlers;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;

class RouteManager
{
    public function handle()
    {
        app('router')->matched(function (RouteMatched $event) {
            if (!$this->isAdminArea($event)) {
                return false;
            }

            $route = $event->route;
            $request = $event->request;

            if ($route->parameter('module')) {
                return true;
            }
            if ($resolver = app('scaffold.config')->get('resource.resolver')) {
                $module = \call_user_func_array($resolver, [$route, $request]);
            } else {
                $module = $request->segment(app('scaffold.config')->get('resource.segment', 2));
            }

            $route->setParameter('module', $module);

            return $module;
        });
    }

    /**
     * Check if running under admin area.
     *
     * @param RouteMatched $event
     *
     * @return bool
     */
    protected function isAdminArea(RouteMatched $event)
    {
        if ($action = $event->route->getAction()) {
            return config('administrator.prefix') === Arr::get($action, 'prefix');
        }

        return false;
    }
}
